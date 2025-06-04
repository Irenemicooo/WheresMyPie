<?php
require_once 'config.php';
require_once 'db.php';

// safety measures
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}

// message handling
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// page redirection
function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit();
}

// validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    // at least 8 characters, one uppercase, one lowercase, and one number
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

// file upload handling
function uploadFile($file, $uploadDir) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return false;
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }

    $newFilename = generateRandomString() . '.' . $extension;
    $destination = $uploadDir . '/' . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return false;
    }

    return $newFilename;
}

// Debug function
function debug($data) {
    if (DEBUG) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}
?>