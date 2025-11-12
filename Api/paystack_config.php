<?php
class PayStackConfig {
    // Test Secret Key (replace with your actual PayStack secret key)
    const SECRET_KEY = 'sk_live_3e2f1dbe73eb802d47eddf745674942e05ddc8dc';
    
    // Public Key (for frontend)
    const PUBLIC_KEY = 'pk_live_c04534bd40e9de4b9b16393d1176a1f350ac8abf';
    
    // Base URL for PayStack API
    const BASE_URL = 'https://api.paystack.co';
    
    public static function getHeaders() {
        return [
            'Authorization: Bearer ' . self::SECRET_KEY,
            'Content-Type: application/json'
        ];
    }
}
?>