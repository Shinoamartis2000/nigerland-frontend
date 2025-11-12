<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

class PaymentVerification {
    private $db;
    private $paystackSecretKey;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->paystackSecretKey = getenv('PAYSTACK_SECRET_KEY');
    }
    
    public function verifyPayment($reference) {
        // Verify with Paystack
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->paystackSecretKey,
                "Cache-Control: no-cache",
            ],
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        
        if ($err) {
            return ['status' => 'error', 'message' => $err];
        }
        
        $result = json_decode($response, true);
        
        if ($result['status'] && $result['data']['status'] == 'success') {
            // Update database
            $stmt = $this->db->prepare("
                UPDATE conference_registrations 
                SET status = 'paid', payment_reference = ?
                WHERE reference = ?
            ");
            $stmt->execute([$result['data']['reference'], $reference]);
            
            return ['status' => 'success', 'data' => $result['data']];
        } else {
            return ['status' => 'failed', 'message' => 'Payment verification failed'];
        }
    }
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['reference'])) {
    $verifier = new PaymentVerification();
    $result = $verifier->verifyPayment($_GET['reference']);
    echo json_encode($result);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Reference parameter required']);
}
?>