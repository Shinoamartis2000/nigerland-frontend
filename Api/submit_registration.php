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
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Get raw POST data
    $input = json_decode(file_get_contents("php://input"), true);
    
    // Log received data for debugging
    error_log("Registration received: " . print_r($input, true));
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Validate required fields
        $required = ['full_name', 'email', 'phone', 'profession', 'payment_method'];
        $missing = [];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            echo json_encode([
                "success" => false,
                "message" => "Missing required fields: " . implode(', ', $missing)
            ]);
            exit;
        }
        
        // Generate unique reference
        $reference = 'NGL-CONF-' . date('Ymd-His') . '-' . rand(100, 999);
        
        // Set default amount if not provided
        $amount = isset($input['amount']) ? floatval($input['amount']) : 25000.00;
        $organization = isset($input['organization']) ? $input['organization'] : '';
        
        // Insert registration
        $query = "INSERT INTO conference_registrations 
                 (reference, full_name, email, phone, profession, organization, payment_method, amount, status) 
                 VALUES (:reference, :full_name, :email, :phone, :profession, :organization, :payment_method, :amount, 'pending')";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':reference', $reference);
        $stmt->bindParam(':full_name', $input['full_name']);
        $stmt->bindParam(':email', $input['email']);
        $stmt->bindParam(':phone', $input['phone']);
        $stmt->bindParam(':profession', $input['profession']);
        $stmt->bindParam(':organization', $organization);
        $stmt->bindParam(':payment_method', $input['payment_method']);
        $stmt->bindParam(':amount', $amount);
        
        if ($stmt->execute()) {
            $registration_id = $db->lastInsertId();
            
            echo json_encode([
                "success" => true,
                "message" => "Registration submitted successfully",
                "data" => [
                    "id" => $registration_id,
                    "reference" => $reference,
                    "full_name" => $input['full_name'],
                    "email" => $input['email'],
                    "amount" => $amount,
                    "payment_method" => $input['payment_method'],
                    "status" => "pending"
                ]
            ]);
        } else {
            $errorInfo = $stmt->errorInfo();
            echo json_encode([
                "success" => false,
                "message" => "Failed to submit registration",
                "error" => $errorInfo[2]
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
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>