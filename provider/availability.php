<?php
require_once '../config/config.php';
$page_title = 'Availability - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('provider');

require_once '../classes/Provider.php';

$user = getCurrentUser();
$provider = new Provider();

$error_message = '';
$success_message = '';

// Get current availability
$availability = $provider->getProviderAvailability($user['user_id']);
$currentAvailability = $availability['Prov_Avail'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $availability_text = trim($_POST['availability'] ?? '');
    
    if (empty($availability_text)) {
        $error_message = 'Please enter your availability information.';
    } else {
        $result = $provider->updateProviderAvailability($user['user_id'], $availability_text);
        
        if ($result['success']) {
            $success_message = $result['message'];
            $currentAvailability = $availability_text;
        } else {
            $error_message = $result['message'];
        }
    }
}

// Predefined availability templates
$templates = [
    'Mon-Fri: 9am-5pm',
    'Mon-Wed-Fri: 8am-4pm',
    'Tue-Thu: 10am-6pm',
    'Weekends Only: 9am-1pm',
    'Mon-Fri: 7am-3pm',
    'Flexible Hours',
    'Emergency Only'
];

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-clock me-2"></i>Update Availability
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

                    <div class="mb-4">
                        <p class="text-muted">
                            Set your availability schedule to help patients book appointments at convenient times. 
                            You can use the templates below or create your own custom schedule.
                        </p>
                    </div>

                    <!-- Quick Templates -->
                    <div class="mb-4">
                        <h6>Quick Templates</h6>
                        <div class="row g-2">
                            <?php foreach ($templates as $template): ?>
                                <div class="col-md-6 col-lg-4">
                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100 template-btn" 
                                            data-template="<?php echo htmlspecialchars($template); ?>">
                                        <?php echo htmlspecialchars($template); ?>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="availability" class="form-label">Availability Schedule *</label>
                            <textarea class="form-control" id="availability" name="availability" rows="5" 
                                      placeholder="e.g., Mon-Fri: 9am-5pm, Sat: 9am-1pm, Closed Sundays" required><?php echo htmlspecialchars($currentAvailability); ?></textarea>
                            <div class="form-text">
                                Be specific about your available days and times. This helps patients understand when they can book appointments.
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Availability Guidelines</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Include specific days and time ranges</li>
                                <li><i class="fas fa-check text-success me-2"></i>Mention any special conditions or notes</li>
                                <li><i class="fas fa-check text-success me-2"></i>Update regularly to reflect changes</li>
                                <li><i class="fas fa-check text-success me-2"></i>Be clear about emergency availability</li>
                            </ul>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Availability
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Current Schedule Preview -->
            <?php if ($currentAvailability): ?>
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt me-2 text-info"></i>Current Schedule
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This is how your availability appears to patients:
                        </div>
                        <div class="bg-light p-3 rounded">
                            <strong>Dr. <?php echo htmlspecialchars($user['name']); ?></strong><br>
                            <span class="text-muted"><?php echo nl2br(htmlspecialchars($currentAvailability)); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tips Card -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>Pro Tips
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-clock me-2 text-primary"></i>Time Management</h6>
                            <ul class="small">
                                <li>Block time for administrative tasks</li>
                                <li>Allow buffer time between appointments</li>
                                <li>Consider lunch breaks and personal time</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-users me-2 text-success"></i>Patient Experience</h6>
                            <ul class="small">
                                <li>Be consistent with your schedule</li>
                                <li>Update availability for holidays</li>
                                <li>Communicate changes in advance</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Template button functionality
    const templateButtons = document.querySelectorAll('.template-btn');
    const availabilityTextarea = document.getElementById('availability');
    
    templateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const template = this.dataset.template;
            availabilityTextarea.value = template;
            availabilityTextarea.focus();
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
