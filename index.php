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
    <title>Healthcare Appointment Management System - NeoHealth Systems</title>
    <meta name="description" content="Professional Healthcare Appointment Management System with AI Integration">
    <meta name="keywords" content="healthcare, appointments, medical, management, AI">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-heartbeat me-2"></i>NeoHealth AMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-3 ms-2" href="auth/register.php">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="main-container">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="hero-content">
                        <h1 class="display-4 fw-bold text-gradient mb-4">
                            Healthcare Management Powered by AI
                        </h1>
                        <p class="lead mb-4 text-muted">
                            Transform your healthcare practice with our intelligent appointment management system. 
                            Experience seamless scheduling, real-time analytics, and AI-powered insights.
                        </p>
                        <div class="row mb-4">
                            <div class="col-sm-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon bg-primary bg-opacity-10 p-2 rounded me-3">
                                        <i class="fas fa-robot text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">AI Assistant</h6>
                                        <small class="text-muted">24/7 intelligent support</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon bg-success bg-opacity-10 p-2 rounded me-3">
                                        <i class="fas fa-calendar-check text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Smart Scheduling</h6>
                                        <small class="text-muted">Automated optimization</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon bg-info bg-opacity-10 p-2 rounded me-3">
                                        <i class="fas fa-chart-analytics text-info"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Real-time Analytics</h6>
                                        <small class="text-muted">Data-driven insights</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon bg-warning bg-opacity-10 p-2 rounded me-3">
                                        <i class="fas fa-shield-alt text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">HIPAA Compliant</h6>
                                        <small class="text-muted">Enterprise security</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="auth/login.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Access Dashboard
                            </a>
                            <a href="auth/register.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Start Free Trial
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="glass-card p-4 text-center">
                                <h5 class="mb-4">Quick Access Portals</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <a href="auth/login.php?role=patient" class="btn btn-outline-primary w-100 py-3">
                                            <i class="fas fa-user fa-2x mb-2 d-block"></i>
                                            <span class="fw-semibold">Patient</span>
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="auth/login.php?role=provider" class="btn btn-outline-success w-100 py-3">
                                            <i class="fas fa-user-md fa-2x mb-2 d-block"></i>
                                            <span class="fw-semibold">Provider</span>
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="auth/login.php?role=admin" class="btn btn-outline-info w-100 py-3">
                                            <i class="fas fa-cog fa-2x mb-2 d-block"></i>
                                            <span class="fw-semibold">Admin</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stats-card">
                                <div class="stats-number">1,247</div>
                                <div class="text-muted">Active Patients</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stats-card">
                                <div class="stats-number">89</div>
                                <div class="text-muted">Healthcare Providers</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stats-card">
                                <div class="stats-number">3,456</div>
                                <div class="text-muted">Appointments This Month</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stats-card">
                                <div class="stats-number">98.7%</div>
                                <div class="text-muted">System Uptime</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12" data-aos="fade-up">
                    <h2 class="display-5 fw-bold text-gradient mb-4">Advanced Healthcare Technology</h2>
                    <p class="lead text-muted">Experience the future of healthcare management with cutting-edge features</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="dashboard-card text-center h-100">
                        <div class="icon mx-auto">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h5 class="fw-bold">AI-Powered Assistant</h5>
                        <p class="text-muted">Intelligent chatbot provides 24/7 support for scheduling, queries, and medical guidance using advanced natural language processing.</p>
                        <div class="mt-auto">
                            <span class="badge bg-primary rounded-pill">Smart Technology</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="dashboard-card text-center h-100">
                        <div class="icon mx-auto">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h5 class="fw-bold">Smart Scheduling</h5>
                        <p class="text-muted">Automated appointment optimization prevents conflicts, reduces wait times, and maximizes provider efficiency with intelligent algorithms.</p>
                        <div class="mt-auto">
                            <span class="badge bg-success rounded-pill">Automation</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="dashboard-card text-center h-100">
                        <div class="icon mx-auto">
                            <i class="fas fa-chart-analytics"></i>
                        </div>
                        <h5 class="fw-bold">Real-time Analytics</h5>
                        <p class="text-muted">Comprehensive dashboards provide insights into patient patterns, provider performance, and operational metrics for data-driven decisions.</p>
                        <div class="mt-auto">
                            <span class="badge bg-info rounded-pill">Data Insights</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="dashboard-card text-center h-100">
                        <div class="icon mx-auto">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h5 class="fw-bold">Mobile-First Design</h5>
                        <p class="text-muted">Responsive interface works seamlessly across all devices, ensuring accessibility for patients and providers anywhere, anytime.</p>
                        <div class="mt-auto">
                            <span class="badge bg-warning rounded-pill">Responsive</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="dashboard-card text-center h-100">
                        <div class="icon mx-auto">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5 class="fw-bold">HIPAA Compliance</h5>
                        <p class="text-muted">Enterprise-grade security with end-to-end encryption, audit trails, and compliance monitoring ensures patient data protection.</p>
                        <div class="mt-auto">
                            <span class="badge bg-danger rounded-pill">Security</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                    <div class="dashboard-card text-center h-100">
                        <div class="icon mx-auto">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h5 class="fw-bold">Integrated Payments</h5>
                        <p class="text-muted">Seamless payment processing with multiple gateway support, automated billing, and financial reporting for streamlined revenue management.</p>
                        <div class="mt-auto">
                            <span class="badge bg-secondary rounded-pill">Finance</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="display-5 fw-bold mb-4">Built for Modern Healthcare</h2>
                    <p class="lead text-muted mb-4">NeoHealth AMS represents the next generation of healthcare management technology, designed specifically for modern medical practices.</p>
                    <div class="row g-4 mb-4">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                    <i class="fas fa-check text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-semibold">Cloud-Native Architecture</h6>
                                    <small class="text-muted">Scalable, reliable, and always accessible</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-success bg-opacity-10 p-2 rounded me-3">
                                    <i class="fas fa-check text-success"></i>
                                </div>
                                <div>
                                    <h6 class="fw-semibold">API-First Design</h6>
                                    <small class="text-muted">Seamless integration with existing systems</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-info bg-opacity-10 p-2 rounded me-3">
                                    <i class="fas fa-check text-info"></i>
                                </div>
                                <div>
                                    <h6 class="fw-semibold">Real-time Updates</h6>
                                    <small class="text-muted">Instant synchronization across all devices</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-warning bg-opacity-10 p-2 rounded me-3">
                                    <i class="fas fa-check text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="fw-semibold">24/7 Support</h6>
                                    <small class="text-muted">Dedicated healthcare technology experts</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="auth/register.php" class="btn btn-primary">
                            <i class="fas fa-rocket me-2"></i>Start Free Trial
                        </a>
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fas fa-calendar me-2"></i>Schedule Demo
                        </a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="glass-card p-3 text-center">
                                <h3 class="text-gradient fw-bold">99.9%</h3>
                                <small class="text-muted">Uptime Guarantee</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="glass-card p-3 text-center">
                                <h3 class="text-gradient fw-bold">500+</h3>
                                <small class="text-muted">Healthcare Partners</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="glass-card p-3 text-center">
                                <h3 class="text-gradient fw-bold">50M+</h3>
                                <small class="text-muted">Appointments Managed</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="glass-card p-3 text-center">
                                <h3 class="text-gradient fw-bold">4.9★</h3>
                                <small class="text-muted">User Rating</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>NeoHealth AMS</h5>
                    <small class="text-muted">Advanced Healthcare Management Solutions</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">© 2025 NeoHealth Systems. All rights reserved.</small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    </script>
</body>
</html>
