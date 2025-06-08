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
function uploadFile($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid parameters.');
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('File too large.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $ext = array_search(
        $finfo->file($file['tmp_name']),
        [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ],
        true
    );

    if (false === $ext) {
        throw new RuntimeException('Invalid file format.');
    }

    $filename = sprintf(
        '%s-%s.%s',
        uniqid('item-', true),
        bin2hex(random_bytes(8)),
        $ext
    );

    if (!move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return $filename;
}

// Debug function
function debug($data) {
    if (DEBUG) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}

function redirectIfNotLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function getItemStatus($status) {
    $statuses = [
        'available' => 'Available',
        'claimed' => 'Claimed',
        'returned' => 'Returned'
    ];
    return $statuses[$status] ?? 'Unknown';
}

function getPagination($total, $per_page, $current_page) {
    $total_pages = ceil($total / $per_page);
    $current_page = max(1, min($current_page, $total_pages));
    
    return [
        'total' => $total,
        'per_page' => $per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'offset' => ($current_page - 1) * $per_page
    ];
}

function buildPaginationLinks($pagination, $base_url, $params = []) {
    $links = [];
    $query = $params ? '&' . http_build_query($params) : '';
    
    // Previous page
    if ($pagination['current_page'] > 1) {
        $links['prev'] = $base_url . '?page=' . ($pagination['current_page'] - 1) . $query;
    }
    
    // Next page
    if ($pagination['current_page'] < $pagination['total_pages']) {
        $links['next'] = $base_url . '?page=' . ($pagination['current_page'] + 1) . $query;
    }
    
    return $links;
}

function handleError($error_code, $message = null) {
    if (!$message) {
        switch ($error_code) {
            case 404:
                $message = 'Page not found';
                break;
            case 403:
                $message = 'Access denied';
                break;
            default:
                $message = 'An error occurred';
        }
    }
    
    redirect('/errors/error.php?code=' . $error_code . '&message=' . urlencode($message));
}

function getImageUrl($path) {
    if (empty($path)) return '';
    return BASE_URL . '/' . ltrim($path, '/');
}

function getAssetUrl($path) {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}
?>