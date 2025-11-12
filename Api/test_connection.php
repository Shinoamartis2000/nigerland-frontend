<?php
header('Content-Type: application/json');

try {
    require_once(__DIR__ . '/config/database.php');
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Test query to check if tables exist
    $testQuery = "SHOW TABLES LIKE 'conference_registrations'";
    $stmt = $db->prepare($testQuery);
    $stmt->execute();
    
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "success" => true,
        "message" => "Database connection successful",
        "database" => "nigerland_conference",
        "tables" => [
            "conference_registrations" => $tableExists ? "exists" : "missing"
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed",
        "error" => $e->getMessage()
    ]);
}
?>