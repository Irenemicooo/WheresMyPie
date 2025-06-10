<?php
// Database configuration
define('DB_HOST', 'localhost');  // change IP in production
define('DB_NAME', 'WheresMyPie');
define('DB_USER', 'pieuser');  // Change in production
define('DB_PASS', 'treenopie');      // Change in production

// Application settings
define('APP_NAME', 'Where\'s My Pie?');
define('BASE_URL', '/WheresMyPie');  // 移除 public_html
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// File upload settings
define('UPLOADS_DIR', __DIR__ . '/../uploads/');
define('ITEMS_UPLOAD_DIR', UPLOADS_DIR . 'items/');
define('EVIDENCE_UPLOAD_DIR', UPLOADS_DIR . 'evidence/');
define('PROFILES_UPLOAD_DIR', UPLOADS_DIR . 'profiles/');
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Security settings
define('DEBUG', false);      // Set false in production
define('HASH_COST', 10);    // Password hashing cost

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);  // Set to 1 if using HTTPS
session_set_cookie_params(['samesite' => 'Strict']);

// Error reporting
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

session_start();
?>