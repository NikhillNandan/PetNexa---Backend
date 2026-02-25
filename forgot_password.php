<?php
/**
 * FORGOT PASSWORD API
 * Handles OTP-based password reset for all user roles
 * 
 * Actions:
 *   ?action=send_otp     - Send OTP to user's email
 *   ?action=verify_otp   - Verify the OTP code
 *   ?action=reset_password - Reset password after OTP verified
 */

header('Content-Type: application/json');
require_once 'db.php';
require_once 'smtp_config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'send_otp':
        sendOtp();
        break;
    case 'verify_otp':
        verifyOtp();
        break;
    case 'reset_password':
        resetPassword();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// ========================================
// Ensure OTP columns exist in users table
// ========================================
function ensureOtpColumns() {
    global $conn;
    
    // Check if reset_otp column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'reset_otp'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE users ADD COLUMN reset_otp VARCHAR(6) DEFAULT NULL");
        $conn->query("ALTER TABLE users ADD COLUMN otp_expires_at DATETIME DEFAULT NULL");
    }
}

// ========================================
// FUNCTION: Send OTP
// ========================================
function sendOtp() {
    global $conn;
    ensureOtpColumns();
    
    $data = json_decode(file_get_contents('php://input'), true);
    $email = isset($data['email']) ? trim($data['email']) : '';
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        return;
    }
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT user_id, full_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'No account found with this email']);
        return;
    }
    
    $user = $result->fetch_assoc();
    $userName = $user['full_name'];
    
    // Generate 6-digit OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Set expiry to 10 minutes from now
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Store OTP in users table
    $stmt = $conn->prepare("UPDATE users SET reset_otp = ?, otp_expires_at = ? WHERE email = ?");
    $stmt->bind_param("sss", $otp, $expiresAt, $email);
    $stmt->execute();
    
    // Send OTP via email
    if (sendOtpEmail($email, $userName, $otp)) {
        echo json_encode([
            'success' => true, 
            'message' => 'OTP sent to your email'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP email. Please try again.']);
    }
}

// ========================================
// FUNCTION: Verify OTP
// ========================================
function verifyOtp() {
    global $conn;
    ensureOtpColumns();
    
    $data = json_decode(file_get_contents('php://input'), true);
    $email = isset($data['email']) ? trim($data['email']) : '';
    $otp = isset($data['otp']) ? trim(strval($data['otp'])) : '';
    
    if (empty($email) || empty($otp)) {
        echo json_encode(['success' => false, 'message' => 'Email and OTP are required']);
        return;
    }
    
    $stmt = $conn->prepare("SELECT reset_otp, otp_expires_at FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }
    
    $user = $result->fetch_assoc();
    
    if ($user['reset_otp'] === null) {
        echo json_encode(['success' => false, 'message' => 'No OTP was requested. Please request a new one.']);
        return;
    }
    
    if (strtotime($user['otp_expires_at']) < time()) {
        // Clear expired OTP
        $stmt = $conn->prepare("UPDATE users SET reset_otp = NULL, otp_expires_at = NULL WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
        return;
    }
    
    if (strval($user['reset_otp']) !== strval($otp)) {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP. Please try again.']);
        return;
    }
    
    echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
}

// ========================================
// FUNCTION: Reset Password
// ========================================
function resetPassword() {
    global $conn;
    ensureOtpColumns();
    
    $data = json_decode(file_get_contents('php://input'), true);
    $email = isset($data['email']) ? trim($data['email']) : '';
    $otp = isset($data['otp']) ? trim(strval($data['otp'])) : '';
    $newPassword = isset($data['new_password']) ? $data['new_password'] : '';
    
    if (empty($email) || empty($otp) || empty($newPassword)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    // Verify OTP one more time
    $stmt = $conn->prepare("SELECT reset_otp, otp_expires_at FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }
    
    $user = $result->fetch_assoc();
    
    if (strval($user['reset_otp']) !== strval($otp) || strtotime($user['otp_expires_at']) < time()) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
        return;
    }
    
    // Update password and clear OTP
    $stmt = $conn->prepare("UPDATE users SET password_hash = ?, reset_otp = NULL, otp_expires_at = NULL WHERE email = ?");
    $stmt->bind_param("ss", $newPassword, $email);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reset password']);
    }
}

// ========================================
// HELPER: Send OTP Email via PHPMailer
// ========================================
function sendOtpEmail($toEmail, $userName, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $userName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Petnexa - Password Reset OTP';
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px;">
            <div style="background: linear-gradient(135deg, #155DFC, #6366f1); padding: 30px; border-radius: 16px 16px 0 0; text-align: center;">
                <h1 style="color: #fff; margin: 0; font-size: 24px;">🐾 Petnexa</h1>
                <p style="color: #E0E7FF; margin: 5px 0 0;">Password Reset Request</p>
            </div>
            <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 16px 16px; border: 1px solid #e5e7eb;">
                <p style="color: #374151;">Hi <strong>' . htmlspecialchars($userName) . '</strong>,</p>
                <p style="color: #374151;">You requested to reset your password. Use the OTP below:</p>
                <div style="background: #fff; border: 2px dashed #155DFC; border-radius: 12px; padding: 20px; text-align: center; margin: 20px 0;">
                    <span style="font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #155DFC;">' . $otp . '</span>
                </div>
                <p style="color: #6b7280; font-size: 14px;">This OTP is valid for <strong>10 minutes</strong>.</p>
                <p style="color: #6b7280; font-size: 14px;">If you did not request this, please ignore this email.</p>
                <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">
                <p style="color: #9ca3af; font-size: 12px; text-align: center;">Petnexa - Your Pet Companion App</p>
            </div>
        </div>';
        $mail->AltBody = "Hi $userName, your Petnexa password reset OTP is: $otp. Valid for 10 minutes.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
