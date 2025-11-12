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
        $required = ['full_name', 'email', 'subject', 'message'];
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
        
        // Insert contact submission
        $query = "INSERT INTO contact_submissions 
                 (full_name, email, subject, message, status, submitted_at) 
                 VALUES (:full_name, :email, :subject, :message, 'new', NOW())";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':full_name', $input['full_name']);
        $stmt->bindParam(':email', $input['email']);
        $stmt->bindParam(':subject', $input['subject']);
        $stmt->bindParam(':message', $input['message']);
        
        if ($stmt->execute()) {
            $submission_id = $db->lastInsertId();
            
            // Optional: Send email notification
            sendEmailNotification($input);
            
            echo json_encode([
                "success" => true,
                "message" => "Thank you for your message! We'll get back to you soon.",
                "submission_id" => $submission_id
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to submit your message. Please try again."
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
        'error' => $e->getMessage()
    ]);
}

function sendEmailNotification($data) {
    $to = "info@nigerlandconsult.com"; // Your email
    $subject = "New Contact Form Submission: " . $data['subject'];
    
    $message = "
    New contact form submission received:\n\n
    Name: {$data['full_name']}\n
    Email: {$data['email']}\n
    Subject: {$data['subject']}\n
    Message: {$data['message']}\n\n
    Submitted at: " . date('Y-m-d H:i:s') . "
    ";
    
    $headers = "From: noreply@nigerlandconsult.com\r\n";
    $headers .= "Reply-To: {$data['email']}\r\n";
    
    // Uncomment to actually send email
    // mail($to, $subject, $message, $headers);
}
?>