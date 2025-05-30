<?php
$page_title = 'Book Appointment - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('patient');

require_once '../classes/Provider.php';
require_once '../classes/Appointment.php';

$user = getCurrentUser();
$provider = new Provider();
$appointment = new Appointment();

$error_message = '';
$success_message = '';

// Get available providers and specialties
$providers = $provider->getAllProviders();
$specialties = $provider->getSpecialties();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $provider_id = $_POST['provider_id'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    
    if (empty($provider_id) || empty($appointment_date) || empty($appointment_time)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        $datetime = $appointment_date . ' ' . $appointment_time . ':00';
        
        $result = $appointment->createAppointment([
            'patient_id' => $user['id'],
            'provider_id' => $provider_id,
            'datetime' => $datetime
        ]);
        
        if ($result['success']) {
            $success_message = $result['message'];
            $_POST = []; // Clear form
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
                        <i class="fas fa-calendar-plus me-2"></i>Book New Appointment
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

                    <form method="POST" id="appointmentForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="specialty" class="form-label">Specialty (Optional)</label>
                                <select class="form-select" id="specialty" name="specialty">
                                    <option value="">All Specialties</option>
                                    <?php foreach ($specialties as $spec): ?>
                                        <option value="<?php echo htmlspecialchars($spec['Prov_Spec']); ?>"
                                                <?php echo (isset($_POST['specialty']) && $_POST['specialty'] === $spec['Prov_Spec']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($spec['Prov_Spec']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="provider_id" class="form-label">Healthcare Provider *</label>
                                <select class="form-select" id="provider_id" name="provider_id" required>
                                    <option value="">Select a provider</option>
                                    <?php foreach ($providers as $prov): ?>
                                        <option value="<?php echo $prov['Prov_ID']; ?>"
                                                data-specialty="<?php echo htmlspecialchars($prov['Prov_Spec']); ?>"
                                                <?php echo (isset($_POST['provider_id']) && $_POST['provider_id'] == $prov['Prov_ID']) ? 'selected' : ''; ?>>
                                            Dr. <?php echo htmlspecialchars($prov['Prov_Name']); ?> - <?php echo htmlspecialchars($prov['Prov_Spec']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="appointment_date" class="form-label">Appointment Date *</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                       min="<?php echo date('Y-m-d'); ?>" 
                                       value="<?php echo htmlspecialchars($_POST['appointment_date'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="appointment_time" class="form-label">Appointment Time *</label>
                                <select class="form-select" id="appointment_time" name="appointment_time" required>
                                    <option value="">Select date and provider first</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Any specific concerns or requests..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-calendar-check me-2"></i>Book Appointment
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Provider Information Card -->
            <div class="card border-0 shadow-sm mt-4" id="providerInfo" style="display: none;">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-md me-2 text-primary"></i>Provider Information
                    </h5>
                </div>
                <div class="card-body">
                    <div id="providerDetails">
                        <!-- Provider details will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const specialtySelect = document.getElementById('specialty');
    const providerSelect = document.getElementById('provider_id');
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    const providerInfo = document.getElementById('providerInfo');
    const providerDetails = document.getElementById('providerDetails');

    // Filter providers by specialty
    specialtySelect.addEventListener('change', function() {
        const selectedSpecialty = this.value;
        const options = providerSelect.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
                return;
            }
            
            const optionSpecialty = option.dataset.specialty;
            if (selectedSpecialty === '' || optionSpecialty === selectedSpecialty) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset provider selection if current selection is hidden
        if (providerSelect.value && providerSelect.selectedOptions[0].style.display === 'none') {
            providerSelect.value = '';
            loadTimeSlots();
        }
    });

    // Load time slots when provider or date changes
    providerSelect.addEventListener('change', loadTimeSlots);
    dateInput.addEventListener('change', loadTimeSlots);

    function loadTimeSlots() {
        const providerId = providerSelect.value;
        const date = dateInput.value;
        
        if (!providerId || !date) {
            timeSelect.innerHTML = '<option value="">Select date and provider first</option>';
            providerInfo.style.display = 'none';
            return;
        }

        // Show provider info
        showProviderInfo(providerId);

        // Load available time slots
        fetch('../api/availability.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'get_time_slots',
                provider_id: providerId,
                date: date
            })
        })
        .then(response => response.json())
        .then(data => {
            timeSelect.innerHTML = '<option value="">Select a time</option>';
            
            if (data.success && data.time_slots.length > 0) {
                data.time_slots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot;
                    option.textContent = formatTime(slot);
                    timeSelect.appendChild(option);
                });
            } else {
                timeSelect.innerHTML = '<option value="">No available slots</option>';
            }
        })
        .catch(error => {
            console.error('Error loading time slots:', error);
            timeSelect.innerHTML = '<option value="">Error loading slots</option>';
        });
    }

    function showProviderInfo(providerId) {
        const selectedOption = providerSelect.selectedOptions[0];
        if (selectedOption && selectedOption.value) {
            const providerName = selectedOption.textContent.split(' - ')[0];
            const specialty = selectedOption.dataset.specialty;
            
            providerDetails.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>${providerName}</h6>
                        <p class="text-muted mb-0">${specialty}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Please arrive 15 minutes before your appointment time.
                        </small>
                    </div>
                </div>
            `;
            
            providerInfo.style.display = 'block';
        }
    }

    function formatTime(time) {
        const [hours, minutes] = time.split(':');
        const hour12 = ((parseInt(hours) + 11) % 12 + 1);
        const ampm = parseInt(hours) >= 12 ? 'PM' : 'AM';
        return `${hour12}:${minutes} ${ampm}`;
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
