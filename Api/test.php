<?php
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'API is working!',
    'php_version' => phpversion(),
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD']
]);
?>