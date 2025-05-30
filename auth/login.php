<?php
session_start();
require_once '../config/config.php';

$error_message = '';
$success_message = '';

// Check for timeout
if (isset($_GET['timeout'])) {
    $error_message = 'Your session has expired. Please login again.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../classes/User.php';
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    if (empty($email) || empty($password) || empty($role)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        $user = new User();
        $login_result = $user->login($email, $password, $role);
        
        if ($login_result['success']) {
            $_SESSION['user_id'] = $login_result['user_id'];
            $_SESSION['user_name'] = $login_result['user_name'];
            $_SESSION['user_email'] = $login_result['user_email'];
            $_SESSION['user_role'] = $login_result['user_role'];
            $_SESSION['last_activity'] = time();
            
            // Redirect based on role
            switch ($role) {
                case 'patient':
                    header('Location: ../patient/dashboard.php');
                    break;
                case 'provider':
                    header('Location: ../provider/dashboard.php');
                    break;
                case 'admin':
                    header('Location: ../admin/dashboard.php');
                    break;
                default:
                    header('Location: ../index.php');
            }
            exit();
        } else {
            $error_message = $login_result['message'];
        }
    }
}

$page_title = 'Login - ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="container-fluid h-100">
        <div class="row h-100">
            <div class="col-lg-6 d-none d-lg-block auth-bg">
                <div class="auth-overlay">
                    <div class="auth-content">
                        <h2 class="text-white mb-4">Welcome Back!</h2>
                        <p class="text-white-50">Access your healthcare appointment management dashboard</p>
                        <div class="auth-features">
                            <div class="feature-item">
                                <i class="fas fa-shield-alt text-white"></i>
                                <span class="text-white">Secure Access</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-calendar-check text-white"></i>
                                <span class="text-white">Easy Scheduling</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-bell text-white"></i>
                                <span class="text-white">Smart Notifications</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-center">
                <div class="auth-form-container">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-3x text-primary mb-3"></i>
                        <h3>Sign In</h3>
                        <p class="text-muted">Enter your credentials to access your account</p>
                    </div>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="auth-form">
                        <div class="mb-3">
                            <label for="role" class="form-label">Login As</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select your role</option>
                                <option value="patient" <?php echo (isset($_POST['role']) && $_POST['role'] === 'patient') ? 'selected' : ''; ?>>Patient</option>
                                <option value="provider" <?php echo (isset($_POST['role']) && $_POST['role'] === 'provider') ? 'selected' : ''; ?>>Healthcare Provider</option>
                                <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-2">
                                <a href="#" class="text-decoration-none">Forgot your password?</a>
                            </p>
                            <p>
                                Don't have an account? 
                                <a href="register.php" class="text-decoration-none">Sign up here</a>
                            </p>
                            <a href="../index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Home
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Pre-select role if provided in URL
        const urlParams = new URLSearchParams(window.location.search);
        const roleParam = urlParams.get('role');
        if (roleParam) {
            document.getElementById('role').value = roleParam;
        }
    </script>
</body>
</html>
