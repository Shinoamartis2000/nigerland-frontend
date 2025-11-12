<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
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
        // Handle registration data
        if (!empty($input)) {
            $query = "INSERT INTO registrations 
                     (full_name, email, phone, institution, position, payment_status, created_at) 
                     VALUES (:full_name, :email, :phone, :institution, :position, 'pending', NOW())";
            
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(':full_name', $input['full_name']);
            $stmt->bindParam(':email', $input['email']);
            $stmt->bindParam(':phone', $input['phone']);
            $stmt->bindParam(':institution', $input['institution']);
            $stmt->bindParam(':position', $input['position']);
            
            if ($stmt->execute()) {
                $registration_id = $db->lastInsertId();
                
                echo json_encode([
                    "success" => true,
                    "message" => "Registration submitted successfully",
                    "registration_id" => $registration_id,
                    "data" => [
                        "id" => $registration_id,
                        "full_name" => $input['full_name'],
                        "email" => $input['email'],
                        "payment_status" => "pending"
                    ]
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to submit registration"
                ]);
            }
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No data received"
            ]);
        }
    } else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // Handle GET request to fetch registrations
        $query = "SELECT * FROM registrations ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            "success" => true,
            "count" => count($registrations),
            "data" => $registrations
        ]);
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