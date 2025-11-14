<?php
require_once 'paystack_config.php';
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = $data['email'] ?? '';
    $amount = $data['amount'] ?? 0;
    $full_name = $data['full_name'] ?? '';
    $phone = $data['phone'] ?? '';
    $profession = $data['profession'] ?? '';
    $organization = $data['organization'] ?? '';
    $payment_method = $data['payment_method'] ?? 'paystack';
    $conference_id = $data['conference_id'] ?? '';
    $conference_title = $data['conference_title'] ?? '';
    $conference_date = $data['conference_date'] ?? '';
    
    // Validate required fields
    if (empty($email) || empty($amount) || empty($full_name)) {
        throw new Exception('Missing required fields');
    }
    
    // Generate unique reference
    $registration_reference = 'CONF-' . strtoupper(uniqid());
    
    // Save registration to database first
    $stmt = $pdo->prepare("
        INSERT INTO conference_registrations 
        (reference, full_name, email, phone, profession, organization, payment_method, amount, conference_id, conference_title, conference_date, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $stmt->execute([
        $registration_reference,
        $full_name,
        $email,
        $phone,
        $profession,
        $organization,
        $payment_method,
        $amount,
        $conference_id,
        $conference_title,
        $conference_date
    ]);
    
    // If bank transfer, return success
    if ($payment_method === 'bank_transfer' || $payment_method === 'bank-transfer') {
        echo json_encode([
            'success' => true,
            'message' => 'Registration received. Please proceed with bank transfer.',
            'reference' => $registration_reference,
            'redirect_url' => 'payment-success.html?reference=' . $registration_reference . '&method=bank_transfer'
        ]);
        exit;
    }
    
    // For Paystack payment, initialize transaction
    $url = PayStackConfig::BASE_URL . '/transaction/initialize';
    
    // Get the base URL dynamically
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $base_url = $protocol . '://' . $_SERVER['HTTP_HOST'];
    
    // Paystack requires amount in kobo (smallest currency unit)
    // So ₦1,500 = 150,000 kobo
    $amount_in_kobo = $amount * 100;
    
    $fields = [
        'email' => $email,
        'amount' => $amount_in_kobo,
        'reference' => $registration_reference,
        'callback_url' => $base_url . '/payment-verification.html?reference=' . $registration_reference,
        'metadata' => [
            'custom_fields' => [
                [
                    'display_name' => "Conference Registration",
                    'variable_name' => "conference_registration",
                    'value' => $conference_title
                ],
                [
                    'display_name' => "Customer Name",
                    'variable_name' => "customer_name",
                    'value' => $full_name
                ]
            ]
        ]
    ];
    
    error_log("PayStack request: " . json_encode($fields));
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . PayStackConfig::SECRET_KEY,
        'Content-Type: application/json',
    ]);
    
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($result, true);
    
    if ($httpcode === 200 && $result['status']) {
        // Update registration with PayStack reference
        $stmt = $pdo->prepare("
            UPDATE conference_registrations 
            SET paystack_reference = ?, payment_gateway = 'paystack' 
            WHERE reference = ?
        ");
        $stmt->execute([$registration_reference, $registration_reference]);
        
        error_log("Payment initialized successfully: " . $result['data']['authorization_url']);
        
        echo json_encode([
            "success" => true,
            "message" => "Payment initialized successfully",
            "authorization_url" => $result['data']['authorization_url'],
            "reference" => $registration_reference
        ]);
    } else {
        $error_message = $result['message'] ?? 'Payment initialization failed';
        error_log("PayStack error: " . json_encode($result));
        throw new Exception($error_message);
    }
    
} catch (Exception $e) {
    error_log('Payment initiation error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Payment initiation failed: ' . $e->getMessage()
    ]);
}
?>