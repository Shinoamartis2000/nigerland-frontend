<?php
header("Content-Type: application/json");

// Read POST body JSON
$input = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($input['reference']) || !isset($input['email']) || !isset($input['amount'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields (reference, email, amount)"
    ]);
    exit;
}

$reference = $input['reference'];
$email = $input['email'];
$amount = intval($input['amount']); // must be integer

// Paystack secret key
$secret_key = "sk_live_3e2f1dbe73eb802d47eddf745674942e05ddc8dc";  // << YOUR SECRET KEY

// Prepare payload
$post_fields = json_encode([
    "email" => $email,
    "amount" => $amount, 
    "reference" => $reference
]);

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $post_fields,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $secret_key",
        "Content-Type: application/json"
    ],
    CURLOPT_RETURNTRANSFER => true
]);

$response = curl_exec($curl);

if (curl_errno($curl)) {
    error_log("Paystack CURL Error: " . curl_error($curl));
    echo json_encode(["success" => false, "message" => "Payment gateway connection failed"]);
    exit;
}

curl_close($curl);

// Decode Paystack response
$result = json_decode($response, true);

// Log raw response (VERY IMPORTANT FOR DEBUGGING!)
error_log("PAYSTACK RAW RESPONSE: " . $response);

if (!$result || !isset($result['status'])) {
    echo json_encode(["success" => false, "message" => "Invalid response from Paystack"]);
    exit;
}

// If Paystack says “OK”
if ($result['status']) {
    echo json_encode([
        "success" => true,
        "authorization_url" => $result['data']['authorization_url'],
        "access_code" => $result['data']['access_code'],
        "reference" => $result['data']['reference']
    ]);
    exit;
}

// If Paystack says “ERROR”
echo json_encode([
    "success" => false,
    "message" => $result['message']
]);
exit;
