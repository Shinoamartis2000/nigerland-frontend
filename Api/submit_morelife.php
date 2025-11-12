<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Enable error logging
error_log("MoreLife API called at: " . date('Y-m-d H:i:s'));

try {
    // Get input data
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    error_log("Received data: " . print_r($input, true));
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Validate required fields
        $required = [
            'full_name', 'email', 'phone', 'location', 'age', 'education', 
            'cause', 'duration', 'medication', 'start_month', 'session_type', 'session_price', 'payment_method'
        ];
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Missing required fields: " . implode(', ', $missing),
                "missing_fields" => $missing
            ]);
            exit;
        }
        
        // Validate that at least one challenge is selected
        if (empty($input['challenges']) || !is_array($input['challenges']) || count($input['challenges']) === 0) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Please select at least one challenge area"
            ]);
            exit;
        }
        
        // Generate unique reference
        $reference = 'MORELIFE-' . date('Ymd-His') . '-' . rand(100, 999);
        
        // Connect to database
        require_once(__DIR__ . '/config/database.php');
        $database = new Database();
        $db = $database->getConnection();
        
        // Insert into database
        $query = "INSERT INTO morelife_sessions 
                 (reference, full_name, email, phone, location, age, education_level, 
                  challenges, other_challenge, challenge_cause, challenge_duration, 
                  trigger_incident, on_medication, start_month, session_type, session_price,
                  payment_method, status, created_at) 
                 VALUES (:reference, :full_name, :email, :phone, :location, :age, :education_level,
                         :challenges, :other_challenge, :challenge_cause, :challenge_duration,
                         :trigger_incident, :on_medication, :start_month, :session_type, :session_price,
                         :payment_method, 'pending', NOW())";
        
        error_log("SQL Query: " . $query);
        
        $stmt = $db->prepare($query);
        
        $challenges = !empty($input['challenges']) ? json_encode($input['challenges']) : NULL;
        $other_challenge = !empty($input['other_challenge']) ? $input['other_challenge'] : NULL;
        $trigger_incident = !empty($input['incident']) ? $input['incident'] : NULL;
        
        // Bind parameters
        $stmt->bindParam(':reference', $reference);
        $stmt->bindParam(':full_name', $input['full_name']);
        $stmt->bindParam(':email', $input['email']);
        $stmt->bindParam(':phone', $input['phone']);
        $stmt->bindParam(':location', $input['location']);
        $stmt->bindParam(':age', $input['age']);
        $stmt->bindParam(':education_level', $input['education']);
        $stmt->bindParam(':challenges', $challenges);
        $stmt->bindParam(':other_challenge', $other_challenge);
        $stmt->bindParam(':challenge_cause', $input['cause']);
        $stmt->bindParam(':challenge_duration', $input['duration']);
        $stmt->bindParam(':trigger_incident', $trigger_incident);
        $stmt->bindParam(':on_medication', $input['medication']);
        $stmt->bindParam(':start_month', $input['start_month']);
        $stmt->bindParam(':session_type', $input['session_type']);
        $stmt->bindParam(':session_price', $input['session_price']);
        $stmt->bindParam(':payment_method', $input['payment_method']);
        
        error_log("Bound parameters: " . print_r([
            'reference' => $reference,
            'full_name' => $input['full_name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'location' => $input['location'],
            'age' => $input['age'],
            'education' => $input['education'],
            'challenges' => $challenges,
            'other_challenge' => $other_challenge,
            'cause' => $input['cause'],
            'duration' => $input['duration'],
            'incident' => $trigger_incident,
            'medication' => $input['medication'],
            'start_month' => $input['start_month'],
            'session_type' => $input['session_type'],
            'session_price' => $input['session_price'],
            'payment_method' => $input['payment_method']
        ], true));
        
        if ($stmt->execute()) {
            $session_id = $db->lastInsertId();
            error_log("Database insert successful. ID: " . $session_id);
            
            echo json_encode([
                "success" => true,
                "message" => "MoreLife session application submitted successfully",
                "data" => [
                    "id" => $session_id,
                    "reference" => $reference,
                    "full_name" => $input['full_name'],
                    "email" => $input['email'],
                    "session_type" => $input['session_type'],
                    "session_price" => $input['session_price'],
                    "payment_method" => $input['payment_method'],
                    "status" => "pending"
                ]
            ]);
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("Database insert failed: " . print_r($errorInfo, true));
            throw new Exception("Database insert failed: " . $errorInfo[2]);
        }
    }
    
} catch (Exception $e) {
    error_log("MoreLife submission error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Application submission failed: ' . $e->getMessage()
    ]);
}
?>