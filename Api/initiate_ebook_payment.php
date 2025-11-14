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
    $ebook_id = $data['ebook_id'] ?? '';
    $ebook_title = $data['ebook_title'] ?? '';
    $payment_method = $data['payment_method'] ?? 'paystack';
    
    // Validate required fields
    if (empty($email) || empty($amount) || empty($full_name) || empty($ebook_title)) {
        throw new Exception('Missing required fields');
    }
    
    // Generate unique reference
    $purchase_reference = 'EBOOK-' . strtoupper(uniqid());
    
    // Save purchase to database
    $stmt = $pdo->prepare("
        INSERT INTO ebook_purchases 
        (reference, full_name, email, phone, ebook_title, ebook_id, amount, payment_method, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $stmt->execute([
        $purchase_reference,
        $full_name,
        $email,
        $phone,
        $ebook_title,
        $ebook_id,
        $amount,
        $payment_method
    ]);
    
    
    // If bank transfer, return success
    if ($payment_method === 'bank_transfer' || $payment_method === 'bank-transfer') {
        echo json_encode([
            'success' => true,
            'message' => 'Purchase received. Please proceed with bank transfer.',
            'reference' => $purchase_reference,
            'redirect_url' => 'payment-success.html?reference=' . $purchase_reference . '&method=bank_transfer&type=ebook'
        ]);
        exit;
    }
    
    // For Paystack payment
    $url = PayStackConfig::BASE_URL . '/transaction/initialize';
    
    // Get the base URL dynamically
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $base_url = $protocol . '://' . $_SERVER['HTTP_HOST'];
    
    // Paystack requires amount in kobo
    $amount_in_kobo = $amount * 100;
    
    $fields = [
        'email' => $email,
        'amount' => $amount_in_kobo,
        'reference' => $purchase_reference,
        'callback_url' => $base_url . '/payment-verification.html?reference=' . $purchase_reference,
        'metadata' => [
            'custom_fields' => [
                [
                    'display_name' => "Ebook Purchase",
                    'variable_name' => "ebook_purchase",
                    'value' => $ebook_title
                ],
                [
                    'display_name' => "Customer Name",
                    'variable_name' => "customer_name",
                    'value' => $full_name
                ]
            ]
        ]
    ];
    
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
        // Update purchase with PayStack reference
        $stmt = $pdo->prepare("
            UPDATE ebook_purchases 
            SET paystack_reference = ? 
            WHERE reference = ?
        ");
        $stmt->execute([$purchase_reference, $purchase_reference]);
        
        echo json_encode([
            "success" => true,
            "message" => "Payment initialized successfully",
            "authorization_url" => $result['data']['authorization_url'],
            "reference" => $purchase_reference
        ]);
    } else {
        $error_message = $result['message'] ?? 'Payment initialization failed';
        throw new Exception($error_message);
    }
    
} catch (Exception $e) {
    error_log('Ebook payment error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Payment initiation failed: ' . $e->getMessage()
    ]);
}
?>