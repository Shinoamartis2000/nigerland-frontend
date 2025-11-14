<?php
header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input["email"])) {
    echo json_encode(["success" => false, "message" => "Email is required"]);
    exit;
}

$email = trim($input["email"]);

require_once "../config/database.php"; 

$db = new Database();
$conn = $db->connect();

// Insert into DB
$stmt = $conn->prepare("INSERT INTO newsletter (email) VALUES (?)");

if ($stmt->execute([$email])) {
    echo json_encode(["success" => true, "message" => "Subscribed successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Already subscribed"]);
}
exit;
