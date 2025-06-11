<?php
require_once '../config/config.php';
$page_title = 'Provider Profile - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('provider');

require_once '../classes/Provider.php';

$user = getCurrentUser();
$provider = new Provider();

$error_message = '';
$success_message = '';

// Get current provider data
$providerData = $provider->getProviderById($user['user_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $license_number = trim($_POST['license_number'] ?? '');
    $education = trim($_POST['education'] ?? '');
    $experience_years = (int)($_POST['experience_years'] ?? 0);
    $consultation_fee = (float)($_POST['consultation_fee'] ?? 0);
    
    if (empty($name) || empty($phone) || empty($specialization)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        $updateData = [
            'name' => $name,
            'phone' => $phone,
            'specialization' => $specialization,
            'license_number' => $license_number,
            'education' => $education,
            'experience_years' => $experience_years,
            'consultation_fee' => $consultation_fee
        ];
        
        $result = $provider->updateProvider($user['user_id'], $updateData);
        
        if ($result['success']) {
            $success_message = 'Profile updated successfully!';
            $providerData = $provider->getProviderById($user['user_id']); // Refresh data
        } else {
            $error_message = $result['message'];
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Provider Profile</h1>
                    <p class="text-muted mb-0">Manage your professional information</p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-md text-primary me-2"></i>Professional Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($providerData['prov_name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($providerData['prov_phone'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="specialization" class="form-label">Specialization <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="specialization" name="specialization" 
                                       value="<?php echo htmlspecialchars($providerData['prov_spec'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="license_number" class="form-label">License Number</label>
                                <input type="text" class="form-control" id="license_number" name="license_number" 
                                       value="<?php echo htmlspecialchars($providerData['license_number'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="experience_years" class="form-label">Years of Experience</label>
                                <input type="number" class="form-control" id="experience_years" name="experience_years" 
                                       value="<?php echo (int)($providerData['experience_years'] ?? 0); ?>" min="0" max="50">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="consultation_fee" class="form-label">Consultation Fee ($)</label>
                                <input type="number" class="form-control" id="consultation_fee" name="consultation_fee" 
                                       value="<?php echo (float)($providerData['consultation_fee'] ?? 100); ?>" min="0" step="0.01">
                            </div>
                            
                            <div class="col-12">
                                <label for="education" class="form-label">Education & Qualifications</label>
                                <textarea class="form-control" id="education" name="education" rows="3" 
                                          placeholder="Enter your educational background and qualifications"><?php echo htmlspecialchars($providerData['education'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>