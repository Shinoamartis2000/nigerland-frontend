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
        $required = ['full_name', 'email', 'phone', 'profession', 'training_title', 'training_id', 'training_price'];
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
        $reference = 'TRAIN-' . date('Ymd-His') . '-' . rand(100, 999);
        
        // Insert training registration
        $query = "INSERT INTO training_registrations 
                 (reference, full_name, email, phone, profession, organization, position, 
                  experience, expectations, special_requirements, training_title, training_id, 
                  training_price, status, created_at) 
                 VALUES (:reference, :full_name, :email, :phone, :profession, :organization, :position,
                         :experience, :expectations, :special_requirements, :training_title, :training_id,
                         :training_price, 'pending', NOW())";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':reference', $reference);
        $stmt->bindParam(':full_name', $input['full_name']);
        $stmt->bindParam(':email', $input['email']);
        $stmt->bindParam(':phone', $input['phone']);
        $stmt->bindParam(':profession', $input['profession']);
        $stmt->bindParam(':organization', $input['organization']);
        $stmt->bindParam(':position', $input['position']);
        $stmt->bindParam(':experience', $input['experience']);
        $stmt->bindParam(':expectations', $input['expectations']);
        $stmt->bindParam(':special_requirements', $input['special_requirements']);
        $stmt->bindParam(':training_title', $input['training_title']);
        $stmt->bindParam(':training_id', $input['training_id']);
        $stmt->bindParam(':training_price', $input['training_price']);
        
        if ($stmt->execute()) {
            $registration_id = $db->lastInsertId();
            
            echo json_encode([
                "success" => true,
                "message" => "Training registration submitted successfully",
                "data" => [
                    "id" => $registration_id,
                    "reference" => $reference,
                    "full_name" => $input['full_name'],
                    "email" => $input['email'],
                    "training_title" => $input['training_title'],
                    "training_price" => $input['training_price'],
                    "status" => "pending"
                ]
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to submit training registration"
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