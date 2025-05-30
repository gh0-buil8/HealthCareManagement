<?php
session_start();
require_once 'config/config.php';

// Redirect based on user role if logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? '';
    switch ($role) {
        case 'patient':
            header('Location: patient/dashboard.php');
            break;
        case 'provider':
            header('Location: provider/dashboard.php');
            break;
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        default:
            header('Location: auth/logout.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Appointment Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="landing-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">Healthcare Appointment Management System</h1>
                        <p class="hero-subtitle">Streamline your healthcare appointments with our intelligent scheduling platform. Book, manage, and track appointments with ease.</p>
                        <div class="hero-features">
                            <div class="feature-item">
                                <i class="fas fa-calendar-check text-primary"></i>
                                <span>Easy Scheduling</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-bell text-primary"></i>
                                <span>Smart Reminders</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-shield-alt text-primary"></i>
                                <span>Secure & Private</span>
                            </div>
                        </div>
                        <div class="hero-actions">
                            <a href="auth/login.php" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                            <a href="auth/register.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Register
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <div class="hero-card">
                            <div class="card shadow-lg border-0">
                                <div class="card-body p-4">
                                    <h5 class="card-title text-center mb-4">Quick Access</h5>
                                    <div class="d-grid gap-3">
                                        <a href="auth/login.php?role=patient" class="btn btn-outline-primary">
                                            <i class="fas fa-user me-2"></i>Patient Portal
                                        </a>
                                        <a href="auth/login.php?role=provider" class="btn btn-outline-success">
                                            <i class="fas fa-user-md me-2"></i>Healthcare Provider
                                        </a>
                                        <a href="auth/login.php?role=admin" class="btn btn-outline-info">
                                            <i class="fas fa-cog me-2"></i>Administrator
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-12 mb-5">
                    <h2 class="section-title">Why Choose Our System?</h2>
                    <p class="section-subtitle">Experience the future of healthcare appointment management</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5>24/7 Availability</h5>
                        <p>Book appointments anytime, anywhere with our online platform</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h5>Mobile Friendly</h5>
                        <p>Access your appointments on any device with our responsive design</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5>Analytics Dashboard</h5>
                        <p>Track and analyze appointment patterns with comprehensive reporting</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
