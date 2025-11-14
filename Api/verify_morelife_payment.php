<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once(__DIR__ . '/config/database.php');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $reference = $_GET['reference'] ?? '';
    
    if (empty($reference)) {
        echo json_encode(["success" => false, "message" => "No reference provided"]);
        exit;
    }
    
    // Verify with PayStack API
    $paystack_secret = 'sk_live_3e2f1dbe73eb802d47eddf745674942e05ddc8dc';
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $paystack_secret,
            "Cache-Control: no-cache",
        ],
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        throw new Exception('cURL Error: ' . $err);
    }
    
    $result = json_decode($response, true);
    
    if ($result['status'] && $result['data']['status'] == 'success') {
        // Update database
        $query = "UPDATE morelife_sessions 
                 SET payment_status = 'paid', 
                     status = 'confirmed',
                     updated_at = NOW()
                 WHERE reference = :reference";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':reference', $reference);
        $stmt->execute();
        
        echo json_encode([
            "success" => true,
            "message" => "Payment verified and session confirmed",
            "data" => $result['data']
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Payment verification failed"
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Payment verification error: ' . $e->getMessage()
    ]);
}
?>