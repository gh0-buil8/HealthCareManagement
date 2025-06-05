<?php
// Application Constants
define('APP_NAME', 'Healthcare AMS');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Comprehensive Healthcare Appointment Management System');
define('COMPANY_NAME', 'NeoHealth Systems');
define('SUPPORT_EMAIL', 'support@neohealth.com');
define('BASE_URL', '/');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'test1234');
define('DB_NAME', 'healthcare_ams');

// Security
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);

// Pagination
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// File Upload
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

// System Settings
define('TIMEZONE', 'America/New_York');
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'M d, Y');
define('DISPLAY_DATETIME_FORMAT', 'M d, Y g:i A');

// Feature Flags
define('ENABLE_AI_CHAT', true);
define('ENABLE_NOTIFICATIONS', true);
define('ENABLE_REPORTS', true);
define('ENABLE_ANALYTICS', true);
?>