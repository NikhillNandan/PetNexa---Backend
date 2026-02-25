<?php
header('Content-Type: application/json');
require_once 'db.php';

$userId = intval($_GET['user_id'] ?? 0);

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Provide ?user_id=<id>']);
    exit;
}

// Verify user exists
$check = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
$check->bind_param("i", $userId);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    $check->close();
    exit;
}
$check->close();

$notifications = [
    [
        'type' => 'chat',
        'title' => 'New message from Dr. Sharma',
        'message' => "Hi! Your pet's vaccination schedule looks good. I've reviewed the records you shared and everything is up to date.",
        'data' => json_encode([
            'sender_name' => 'Dr. Sharma',
            'sender_photo' => 'uploads/roles/doctor/doctor1.jpg',
            'last_message' => "Hi! Your pet's vaccination schedule looks good."
        ])
    ],
    [
        'type' => 'appointment',
        'title' => 'Appointment Confirmed',
        'message' => "Your appointment with Dr. Priya Mehta on 22 February 2026 at 10:30 AM has been confirmed.",
        'data' => json_encode([
            'doctor_name' => 'Dr. Priya Mehta',
            'status' => 'Confirmed',
            'appointment_date' => '2026-02-22',
            'appointment_time' => '10:30 AM'
        ])
    ],
    [
        'type' => 'order',
        'title' => 'Order Shipped!',
        'message' => "Great news! Your order #ORD-2026-1847 has been shipped via BlueDart Express.",
        'data' => json_encode([
            'product_name' => 'Royal Canin Maxi Adult Dog Food',
            'status' => 'Shipped',
            'tracking_id' => 'BD9847562130'
        ])
    ],
    [
        'type' => 'booking',
        'title' => 'Spa Booking Accepted',
        'message' => "Pawfect Spa has accepted your grooming booking for Max (Golden Retriever) on 23 February 2026 at 2:00 PM.",
        'data' => json_encode([
            'pet_name' => 'Max',
            'service_name' => 'Full Body Grooming',
            'customer_name' => 'John Doe',
            'customer_phone' => '9876543210',
            'status' => 'Accepted',
            'service_date' => '2026-02-23',
            'service_time' => '02:00 PM',
            'service_fee' => 2050.0,
            'duration_minutes' => 120
        ])
    ],
    [
        'type' => 'certificate',
        'title' => 'Health Certificate Ready',
        'message' => "The health certificate for your pet Buddy has been issued successfully.",
        'data' => json_encode([
            'pet_name' => 'Buddy',
            'certificate_id' => 'HC-2026-00892',
            'file_url' => 'uploads/certificates/hc_892.pdf'
        ])
    ],
    [
        'type' => 'review',
        'title' => 'New Review Received',
        'message' => "⭐⭐⭐⭐⭐ Ravi Kumar left a 5-star review for your grooming service.",
        'data' => json_encode([
            'customer_name' => 'Ravi Kumar',
            'rating' => 5,
            'comment' => 'Amazing grooming service!'
        ])
    ],
    [
        'type' => 'system',
        'title' => 'Welcome to Petnexa!',
        'message' => "Your account is all set up. Explore everything Petnexa has to offer!",
        'data' => null
    ]
];

$inserted = 0;
$errors = [];

$stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, reference_id, data, is_read, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");

foreach ($notifications as $index => $notif) {
    $refId = 1000 + $index; // sample reference IDs
    // Stagger timestamps so they appear at different times
    $minutesAgo = ($index + 1) * 15; // 15, 30, 45... minutes ago
    $timestamp = date('Y-m-d H:i:s', strtotime("-{$minutesAgo} minutes"));
    $notifData = $notif['data'];

    $stmt->bind_param("isssiss", $userId, $notif['title'], $notif['message'], $notif['type'], $refId, $notifData, $timestamp);

    if ($stmt->execute()) {
        $inserted++;
    } else {
        $errors[] = "Failed to insert '{$notif['type']}': " . $stmt->error;
    }
}
$stmt->close();

echo json_encode([
    'success' => $inserted > 0,
    'message' => "$inserted of " . count($notifications) . " test notifications created",
    'inserted' => $inserted,
    'total_types' => count($notifications),
    'types_seeded' => ['chat', 'appointment', 'order', 'booking', 'certificate', 'review', 'system'],
    'errors' => $errors
]);
?>
