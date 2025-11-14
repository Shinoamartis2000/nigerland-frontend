<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

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

    // CHECK EXISTING SUBSCRIBER
    $check = $db->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $check->execute([$email]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        if ($existing['status'] === 'active') {
            echo json_encode(['success' => true, 'message' => 'You are already subscribed']);
            exit();
        }

        $update = $db->prepare("UPDATE newsletter_subscribers SET status='active', subscribed_at=NOW() WHERE email=?");
        $update->execute([$email]);

        echo json_encode(['success' => true, 'message' => 'Subscription reactivated']);
        exit();
    }

    // INSERT NEW
    $insert = $db->prepare("
        INSERT INTO newsletter_subscribers (email, status, source, subscribed_at)
        VALUES (?, 'active', 'website', NOW())
    ");

    if ($insert->execute([$email])) {
        echo json_encode([
            'success' => true,
            'message' => 'Subscription successful!',
            'data' => ['email' => $email]
        ]);
    } else {
        throw new Exception('Database insert failed');
    }

} catch (Exception $e) {
    error_log('Newsletter error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
