<?php
/**
 * Newsletter Subscription API
 * Handles newsletter subscription submissions
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config/database.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validate email
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
    $checkStmt = $pdo->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $checkStmt->execute([$email]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        if ($existing['status'] === 'active') {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'You are already subscribed to our newsletter'
            ]);
            exit();
        } else {
            // Reactivate subscription
            $updateStmt = $pdo->prepare("UPDATE newsletter_subscribers SET status = 'active', subscribed_at = NOW() WHERE email = ?");
            $updateStmt->execute([$email]);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Your subscription has been reactivated'
            ]);
            exit();
        }
    }
    
    // Insert new subscriber
    $insertStmt = $pdo->prepare("
        INSERT INTO newsletter_subscribers (email, status, source, subscribed_at) 
        VALUES (?, 'active', 'website', NOW())
    ");
    
    if ($insertStmt->execute([$email])) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for subscribing! You will receive updates from Nigerland Consult.',
            'data' => [
                'email' => $email,
                'subscribed_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception('Failed to save subscription');
    }
    
} catch (Exception $e) {
    error_log('Newsletter subscription error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again later.',
        'error' => $e->getMessage()
    ]);
}
?>
