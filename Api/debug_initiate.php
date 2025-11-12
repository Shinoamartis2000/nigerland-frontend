<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Simple debug to test if the file is accessible
error_log("DEBUG: initiate_payment.php accessed");

$input = json_decode(file_get_contents("php://input"), true);
error_log("DEBUG: Received data: " . print_r($input, true));

echo json_encode([
    "success" => true,
    "message" => "Debug endpoint working",
    "received_data" => $input,
    "server_info" => [
        "php_version" => PHP_VERSION,
        "method" => $_SERVER['REQUEST_METHOD'],
        "content_type" => $_SERVER['CONTENT_TYPE'] ?? 'Not set'
    ]
]);
?>