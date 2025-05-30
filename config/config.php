<?php
// Application Configuration
define('APP_NAME', 'Healthcare Appointment Management System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/healthcare_ams/');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'healthcare_ams');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: 'your-email@example.com');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: 'your-password');
define('SMTP_FROM', getenv('SMTP_FROM') ?: 'noreply@healthcare-ams.com');

// Security Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);

// File Upload Settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Timezone
date_default_timezone_set('America/New_York');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Auto-load classes
spl_autoload_register(function($class) {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
        header('Location: ' . BASE_URL . 'auth/login.php?timeout=1');
        exit();
    }
}
$_SESSION['last_activity'] = time();

// Security headers
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
?>
