<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once(__DIR__ . '/config/database.php');
require_once(__DIR__ . '/paystack_config.php');

try {
    $input = json_decode(file_get_contents("php://input"), true);
    $reference = $input['reference'] ?? $_GET['reference'] ?? '';
    
    if (empty($reference)) {
        echo json_encode([
            "success" => false,
            "message" => "No reference provided"
        ]);
        exit;
    }
    
    // Verify payment with PayStack
    $url = PayStackConfig::BASE_URL . '/transaction/verify/' . $reference;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, PayStackConfig::getHeaders());
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        throw new Exception('cURL Error: ' . $err);
    }
    
    $result = json_decode($response, true);
    
    if ($result['status'] === true && $result['data']['status'] === 'success') {
        // Payment was successful
        $database = new Database();
        $db = $database->getConnection();
        
        // Update registration status
        $query = "UPDATE conference_registrations 
                 SET status = 'paid', 
                     paystack_status = 'success',
                     payment_reference = :payment_ref,
                     payment_date = NOW(),
                     updated_at = NOW()
                 WHERE paystack_reference = :reference 
                 OR reference = :reference";
        
        $stmt = $db->prepare($query);
        $payment_ref = $result['data']['reference'];
        $stmt->bindParam(':payment_ref', $payment_ref);
        $stmt->bindParam(':reference', $reference);
        $stmt->execute();
        
        echo json_encode([
            "success" => true,
            "message" => "Payment verified successfully",
            "data" => [
                "reference" => $payment_ref,
                "amount" => $result['data']['amount'] / 100,
                "currency" => $result['data']['currency'],
                "paid_at" => $result['data']['paid_at'],
                "transaction_id" => 'TXN-' . $result['data']['id']
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Payment verification failed",
            "data" => $result['data'] ?? null
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Payment verification error',
        'error' => $e->getMessage()
    ]);
}
?>