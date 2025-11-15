<?php
/**
 * Newsletter Debug Script
 * This will tell us exactly what's failing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$debug = [];
$debug['step'] = '1. Starting debug';

// Step 1: Check if database config file exists
$debug['step'] = '2. Checking database config file';
$config_path = __DIR__ . '/config/database.php';
$debug['config_file_exists'] = file_exists($config_path);
$debug['config_path'] = $config_path;

if (!file_exists($config_path)) {
    echo json_encode(['success' => false, 'error' => 'Config file not found', 'debug' => $debug]);
    exit;
}

// Step 2: Try to include config
$debug['step'] = '3. Including config file';
try {
    require_once $config_path;
    $debug['config_loaded'] = 'SUCCESS';
} catch (Exception $e) {
    $debug['config_error'] = $e->getMessage();
    echo json_encode(['success' => false, 'error' => 'Config failed to load', 'debug' => $debug]);
    exit;
}

// Step 3: Check if $pdo exists
$debug['step'] = '4. Checking $pdo variable';
$debug['pdo_exists'] = isset($pdo);
$debug['pdo_type'] = isset($pdo) ? get_class($pdo) : 'NOT SET';

if (!isset($pdo)) {
    echo json_encode(['success' => false, 'error' => '$pdo variable not created by config', 'debug' => $debug]);
    exit;
}

// Step 4: Test database connection
$debug['step'] = '5. Testing database query';
try {
    $test = $pdo->query("SELECT 1 as test");
    $result = $test->fetch(PDO::FETCH_ASSOC);
    $debug['db_query_test'] = $result['test'] === 1 ? 'SUCCESS' : 'FAILED';
} catch (Exception $e) {
    $debug['db_query_error'] = $e->getMessage();
    echo json_encode(['success' => false, 'error' => 'Database query failed', 'debug' => $debug]);
    exit;
}

// Step 5: Check if newsletter table exists
$debug['step'] = '6. Checking newsletter_subscribers table';
try {
    $check_table = $pdo->query("SHOW TABLES LIKE 'newsletter_subscribers'");
    $table_exists = $check_table->fetch();
    $debug['newsletter_table_exists'] = $table_exists ? 'YES' : 'NO';
    
    if (!$table_exists) {
        echo json_encode(['success' => false, 'error' => 'newsletter_subscribers table does not exist', 'debug' => $debug]);
        exit;
    }
} catch (Exception $e) {
    $debug['table_check_error'] = $e->getMessage();
}

// Step 6: Get POST data
$debug['step'] = '7. Checking POST data';
$raw_input = file_get_contents('php://input');
$debug['raw_post_data'] = $raw_input;
$debug['post_data_length'] = strlen($raw_input);

$data = json_decode($raw_input, true);
$debug['decoded_data'] = $data;
$debug['json_error'] = json_last_error_msg();
$debug['email_received'] = isset($data['email']) ? $data['email'] : 'NOT PROVIDED';

// Step 7: Test actual insertion
$debug['step'] = '8. Testing newsletter subscription';
if (!empty($data['email'])) {
    try {
        $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
        
        if (!$email) {
            throw new Exception('Invalid email format');
        }
        
        // Check existing
        $check = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
        $check->execute([$email]);
        $exists = $check->fetch();
        
        if ($exists) {
            $debug['subscription_result'] = 'Email already exists';
        } else {
            // Try insert
            $insert = $pdo->prepare("INSERT INTO newsletter_subscribers (email, status, source, subscribed_at) VALUES (?, 'active', 'website', NOW())");
            $success = $insert->execute([$email]);
            $debug['subscription_result'] = $success ? 'SUCCESS - Inserted' : 'FAILED - Insert returned false';
            $debug['insert_success'] = $success;
        }
    } catch (Exception $e) {
        $debug['subscription_error'] = $e->getMessage();
        $debug['subscription_result'] = 'EXCEPTION: ' . $e->getMessage();
    }
}

// Return all debug info
echo json_encode([
    'success' => true,
    'message' => 'Debug complete',
    'debug' => $debug
], JSON_PRETTY_PRINT);
?>