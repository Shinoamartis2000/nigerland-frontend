<?php
// Load environment variables
function getEnvVariable($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}

// Database configuration - UPDATED FOR FASTHOST
define('DB_HOST', getEnvVariable('DB_HOST', 'mysql-200-132.mysql.prositehosting.net'));
define('DB_NAME', getEnvVariable('DB_NAME', 'nigerland_conference'));
define('DB_USERNAME', getEnvVariable('DB_USERNAME', 'Nigerland'));
define('DB_PASSWORD', getEnvVariable('DB_PASSWORD', 'Homelond2024'));

// Paystack configuration
define('PAYSTACK_PUBLIC_KEY', getEnvVariable('PAYSTACK_PUBLIC_KEY', ''));
define('PAYSTACK_SECRET_KEY', getEnvVariable('PAYSTACK_SECRET_KEY', ''));
define('PAYSTACK_BASE_URL', 'https://api.paystack.co');

// SendGrid configuration
define('SENDGRID_API_KEY', getEnvVariable('SENDGRID_API_KEY', ''));
define('SENDER_EMAIL', getEnvVariable('SENDER_EMAIL', 'umeokekeprince@gmail.com'));
define('SENDER_NAME', getEnvVariable('SENDER_NAME', 'Nigerland Consult'));

// Site configuration - UPDATE THIS TOO
define('SITE_URL', getEnvVariable('SITE_URL', 'http://nigerlandconsult.com'));
?>