<?php
/**
 * Newsletter Subscription API - FIXED
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (empty($data['email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit();
    }

    $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit();
    }

    // Check if email already exists
    $check = $pdo->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $check->execute([$email]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        if ($existing['status'] === 'active') {
            echo json_encode(['success' => true, 'message' => 'You are already subscribed to our newsletter']);
            exit();
        }

        // Reactivate
        $update = $pdo->prepare("UPDATE newsletter_subscribers SET status='active', subscribed_at=NOW() WHERE email=?");
        $update->execute([$email]);

        echo json_encode(['success' => true, 'message' => 'Your subscription has been reactivated']);
        exit();
    }

    // Insert new subscriber
    $insert = $pdo->prepare("
        INSERT INTO newsletter_subscribers (email, status, source, subscribed_at)
        VALUES (?, 'active', 'website', NOW())
    ");

    if ($insert->execute([$email])) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for subscribing! You will receive updates from Nigerland Consult.'
        ]);
    } else {
        throw new Exception('Failed to save subscription');
    }

} catch (Exception $e) {
    error_log('Newsletter error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Subscription failed. Please try again.'
    ]);
}
?>