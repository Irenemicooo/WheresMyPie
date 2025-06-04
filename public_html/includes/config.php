<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'wheremypie');
define('DB_USER', 'user');
define('DB_PASS', 'password');

// Application paths
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/WheresMyPie/public_html');
define('UPLOAD_PATH', '/var/www/html/WheresMyPie/public_html/uploads');
define('LOG_PATH', '/var/log/wheremypie');

// File upload settings
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB for Rpi
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png']);

// Pagination
define('ITEMS_PER_PAGE', 10);

// Production settings
error_reporting(0);
ini_set('display_errors', 0);
define('DEBUG', false);

// Security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.gc_maxlifetime', 3600);


// Memory limits
ini_set('memory_limit', '128M');
ini_set('max_execution_time', 30);

session_start();
?>