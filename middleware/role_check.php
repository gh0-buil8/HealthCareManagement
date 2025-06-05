<?php
require_once 'auth.php';

function requireRole($requiredRole) {
    requireAuth();
    
    $user = getCurrentUser();
    if (!$user || $user['user_role'] !== $requiredRole) {
        header('HTTP/1.1 403 Forbidden');
        echo '<h1>Access Denied</h1><p>You do not have permission to access this page.</p>';
        exit();
    }
}

function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['user_role'] === $role;
}

function hasAnyRole($roles) {
    $user = getCurrentUser();
    return $user && in_array($user['user_role'], $roles);
}
?>