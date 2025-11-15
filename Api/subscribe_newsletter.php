<?php
/**
 * Newsletter Subscription - Simplified Version
 * Direct database connection for reliability
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user, log them instead

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Database credentials (direct connection)
    $host = "mysql-200-132.mysql.prositehosting.net";
    $db_name = "nigerland_conference";
    $username = "Nigerland";
    $password = "Homeland2024";
    
    // Create connection
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
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
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        if ($existing['status'] === 'active') {
            echo json_encode([
                'success' => true, 
                'message' => 'You are already subscribed to our newsletter'
            ]);
            exit();
        } else {
            // Reactivate
            $update = $pdo->prepare("UPDATE newsletter_subscribers SET status = 'active', subscribed_at = NOW() WHERE email = ?");
            $update->execute([$email]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Your subscription has been reactivated'
            ]);
            exit();
        }
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
    
} catch (PDOException $e) {
    error_log('Newsletter DB Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error. Please try again later.'
    ]);
} catch (Exception $e) {
    error_log('Newsletter Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Subscription failed. Please try again.'
    ]);
}
?>