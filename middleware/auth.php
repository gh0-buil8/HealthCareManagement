<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'user_id' => $_SESSION['user_id'],
            'user_name' => $_SESSION['user_name'],
            'user_email' => $_SESSION['user_email'],
            'user_role' => $_SESSION['user_role']
        ];
    }
    return null;
}

function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: /auth/login.php');
        exit();
    }
}

function logout() {
    session_destroy();
    header('Location: /auth/login.php');
    exit();
}

// Auto-logout on session timeout
if (isLoggedIn() && isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > 3600) { // 1 hour timeout
        logout();
    }
}

if (isLoggedIn()) {
    $_SESSION['last_activity'] = time();
}
?>