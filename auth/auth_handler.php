<?php
require_once '../config/config.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user has specific role
function hasRole($required_role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $required_role;
}

// Redirect if not logged in
function requireLogin($redirect_url = '../auth/login.php') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect_url);
        exit();
    }
}

// Redirect if user doesn't have required role
function requireRole($required_role, $redirect_url = '../auth/login.php') {
    requireLogin($redirect_url);
    
    if (!hasRole($required_role)) {
        header('Location: ' . $redirect_url);
        exit();
    }
}

// Get current user info
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }
    return null;
}

// Check if user can access specific resource
function canAccess($resource, $action = 'view') {
    $user = getCurrentUser();
    if (!$user) return false;
    
    $role = $user['role'];
    
    // Define permissions matrix
    $permissions = [
        'admin' => [
            'patients' => ['view', 'create', 'edit', 'delete'],
            'providers' => ['view', 'create', 'edit', 'delete'],
            'appointments' => ['view', 'create', 'edit', 'delete'],
            'payments' => ['view', 'create', 'edit', 'delete'],
            'reports' => ['view', 'create'],
            'notifications' => ['view', 'create', 'edit', 'delete']
        ],
        'provider' => [
            'appointments' => ['view', 'edit'],
            'patients' => ['view'],
            'schedule' => ['view', 'edit'],
            'availability' => ['view', 'edit']
        ],
        'patient' => [
            'appointments' => ['view', 'create', 'edit'],
            'payments' => ['view'],
            'profile' => ['view', 'edit']
        ]
    ];
    
    return isset($permissions[$role][$resource]) && 
           in_array($action, $permissions[$role][$resource]);
}

// Log user activity
function logActivity($action, $details = '') {
    $user = getCurrentUser();
    if ($user) {
        try {
            $db = new Database();
            $stmt = $db->prepare("INSERT INTO user_activity (user_id, action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $user['id'],
                $action,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
}
?>
