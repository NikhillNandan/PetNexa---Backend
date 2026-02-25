<?php
// send_fcm.php — Helper to send FCM push notifications via Firebase Admin SDK (HTTP v1)
require_once 'db.php';

function sendFCMNotification($userId, $title, $message, $type = 'system', $referenceId = null) {
    global $conn;

    // 1. Insert into notifications table
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, reference_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $userId, $title, $message, $type, $referenceId);
    $stmt->execute();
    $notificationId = $stmt->insert_id;
    $stmt->close();

    // 2. Get user's FCM token
    $stmt = $conn->prepare("SELECT fcm_token, role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || empty($user['fcm_token'])) {
        return false; // No FCM token, notification saved to DB only
    }

    // 3. Send FCM push notification
    $accessToken = getFirebaseAccessToken();
    if (!$accessToken) return false;

    $projectId = 'petnexa-6906a';
    $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

    $payload = [
        'message' => [
            'token' => $user['fcm_token'],
            'notification' => [
                'title' => $title,
                'body' => $message
            ],
            'data' => [
                'type' => $type,
                'reference_id' => strval($referenceId ?? ''),
                'notification_id' => strval($notificationId ?? ''),
                'title' => $title,
                'body' => $message,
                'role' => $user['role'],
                'click_action' => 'NOTIFICATION_CLICK'
            ],
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'petnexa_' . $type,
                    'sound' => 'default'
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode == 200;
}

function getFirebaseAccessToken() {
    $keyFile = __DIR__ . '/petnexa-6906a-firebase-adminsdk-fbsvc-bce307b6a6.json';
    if (!file_exists($keyFile)) return null;

    $key = json_decode(file_get_contents($keyFile), true);

    // Create JWT
    $header = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $now = time();
    $claims = base64url_encode(json_encode([
        'iss' => $key['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600
    ]));

    $signature = '';
    openssl_sign("$header.$claims", $signature, $key['private_key'], OPENSSL_ALGO_SHA256);
    $jwt = "$header.$claims." . base64url_encode($signature);

    // Exchange JWT for access token
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return $response['access_token'] ?? null;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
?>
