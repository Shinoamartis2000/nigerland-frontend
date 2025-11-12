<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Log the received data
$input = file_get_contents("php://input");
$data = json_decode($input, true);

error_log("Registration Data Received: " . print_r($data, true));

// Simple response for testing
echo json_encode([
    "success" => true,
    "message" => "Debug: Registration received",
    "received_data" => $data,
    "debug" => [
        "method" => $_SERVER['REQUEST_METHOD'],
        "content_type" => $_SERVER['CONTENT_TYPE'] ?? 'Not set',
        "input_raw" => $input
    ]
]);
?>