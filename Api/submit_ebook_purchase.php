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
    
    $input = json_decode(file_get_contents("php://input"), true);
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Validate required fields
        $required = ['full_name', 'email', 'phone', 'ebook_title', 'amount'];
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
        $reference = 'EBOOK-' . date('Ymd-His') . '-' . rand(100, 999);
        
        // Insert ebook purchase
        $query = "INSERT INTO ebook_purchases 
                 (reference, full_name, email, phone, ebook_title, amount, payment_method, status) 
                 VALUES (:reference, :full_name, :email, :phone, :ebook_title, :amount, :payment_method, 'pending')";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':reference', $reference);
        $stmt->bindParam(':full_name', $input['full_name']);
        $stmt->bindParam(':email', $input['email']);
        $stmt->bindParam(':phone', $input['phone']);
        $stmt->bindParam(':ebook_title', $input['ebook_title']);
        $stmt->bindParam(':amount', $input['amount']);
        $stmt->bindParam(':payment_method', $input['payment_method']);
        
        if ($stmt->execute()) {
            $purchase_id = $db->lastInsertId();
            
            echo json_encode([
                "success" => true,
                "message" => "Purchase submitted successfully",
                "data" => [
                    "id" => $purchase_id,
                    "reference" => $reference,
                    "full_name" => $input['full_name'],
                    "email" => $input['email'],
                    "ebook_title" => $input['ebook_title'],
                    "amount" => $input['amount'],
                    "payment_method" => $input['payment_method'],
                    "status" => "pending"
                ]
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to submit purchase"
            ]);
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}
?>