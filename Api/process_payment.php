<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

try {
    require_once(__DIR__ . '/config/database.php');
    
    $input = json_decode(file_get_contents("php://input"), true);
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        // Validate input
        if (empty($input['reference']) || empty($input['payment_method'])) {
            echo json_encode([
                "success" => false,
                "message" => "Missing required fields"
            ]);
            exit;
        }
        
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if registration exists
        $checkQuery = "SELECT * FROM conference_registrations WHERE reference = :reference";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':reference', $input['reference']);
        $checkStmt->execute();
        
        $registration = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$registration) {
            echo json_encode([
                "success" => false,
                "message" => "Registration not found"
            ]);
            exit;
        }
        
        // Generate payment reference
        $payment_reference = 'PAY-' . $input['reference'] . '-' . rand(1000, 9999);
        
        // Update registration with payment info
        $query = "UPDATE conference_registrations 
                 SET status = 'paid', 
                     payment_reference = :payment_reference,
                     updated_at = NOW()
                 WHERE reference = :reference";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':payment_reference', $payment_reference);
        $stmt->bindParam(':reference', $input['reference']);
        
        if ($stmt->execute()) {
            $response = [
                'success' => true,
                'message' => 'Payment processed successfully',
                'reference' => $payment_reference,
                'transaction_id' => 'TXN-' . uniqid(),
                'data' => [
                    'registration_reference' => $input['reference'],
                    'payment_method' => $input['payment_method'],
                    'amount' => $registration['amount'],
                    'currency' => 'NGN',
                    'status' => 'paid',
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            
            echo json_encode($response);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update payment status'
            ]);
        }
        
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Method not allowed"
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Payment processing error',
        'error' => $e->getMessage()
    ]);
}
?>