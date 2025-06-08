<?php
session_start();

// clear all session variables
$_SESSION = [];

// clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// destroy the session
session_destroy();

// back to login page
header("Location: login.php");
exit;
