<?php
require_once '../config/config.php';
$page_title = 'My Profile - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('patient');

require_once '../classes/User.php';

$user_obj = new User();
$user = getCurrentUser();

$error_message = '';
$success_message = '';

// Get current user data
$userData = $user_obj->getUserById($user['user_id'], $user['user_role']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($name) || empty($phone)) {
        $error_message = 'Name and phone number are required.';
    } else {
        $result = $user_obj->updateProfile($user['id'], $user['role'], [
            'name' => $name,
            'phone' => $phone,
            'address' => $address
        ]);
        
        if ($result['success']) {
            $success_message = $result['message'];
            // Update session data
            $_SESSION['user_name'] = $name;
            // Refresh user data
            $userData = $user_obj->getUserById($user['id'], $user['role']);
        } else {
            $error_message = $result['message'];
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-user-edit me-2"></i>My Profile
                    </h4>
                </div>
                <div class="card-body">
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

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($userData['Pat_Name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($userData['Pat_Email'] ?? ''); ?>" disabled>
                                <small class="form-text text-muted">Email cannot be changed. Contact support if needed.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($userData['Pat_Phone'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="dob" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob" 
                                       value="<?php echo htmlspecialchars($userData['Pat_DOB'] ?? ''); ?>" disabled>
                                <small class="form-text text-muted">Date of birth cannot be changed.</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($userData['Pat_Addr'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Information -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-info"></i>Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Patient ID</label>
                                <p class="mb-0"><?php echo htmlspecialchars($userData['Pat_ID'] ?? ''); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Account Type</label>
                                <p class="mb-0">
                                    <span class="badge bg-primary">Patient</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2 text-warning"></i>Security Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">Change Password</h6>
                            <p class="text-muted mb-0">Update your account password for better security</p>
                        </div>
                        <button class="btn btn-outline-primary" onclick="alert('Password change functionality coming soon!')">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Two-Factor Authentication</h6>
                            <p class="text-muted mb-0">Add an extra layer of security to your account</p>
                        </div>
                        <button class="btn btn-outline-secondary" onclick="alert('2FA setup coming soon!')">
                            <i class="fas fa-mobile-alt me-2"></i>Setup 2FA
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
