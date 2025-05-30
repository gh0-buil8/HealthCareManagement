<?php
$page_title = 'My Appointments - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('patient');

require_once '../classes/Appointment.php';

$user = getCurrentUser();
$appointment = new Appointment();

$error_message = '';
$success_message = '';

// Handle appointment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $appointment_id = $_POST['appointment_id'] ?? '';
    
    if ($action === 'cancel' && $appointment_id) {
        $result = $appointment->cancelAppointment($appointment_id, $user['id'], $user['role']);
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    } elseif ($action === 'reschedule' && $appointment_id) {
        $new_datetime = $_POST['new_datetime'] ?? '';
        if ($new_datetime) {
            $result = $appointment->rescheduleAppointment($appointment_id, $new_datetime, $user['id'], $user['role']);
            if ($result['success']) {
                $success_message = $result['message'];
            } else {
                $error_message = $result['message'];
            }
        }
    }
}

// Get appointments
$appointments = $appointment->getPatientAppointments($user['id'], 50);

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">My Appointments</h1>
                    <p class="text-muted mb-0">View and manage your healthcare appointments</p>
                </div>
                <div>
                    <a href="book_appointment.php" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-2"></i>Book New Appointment
                    </a>
                </div>
            </div>
        </div>
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

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!empty($appointments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Provider</th>
                                <th>Specialty</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $apt): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo date('M j, Y', strtotime($apt['DateTime'])); ?></strong><br>
                                            <small class="text-muted"><?php echo date('g:i A', strtotime($apt['DateTime'])); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>Dr. <?php echo htmlspecialchars($apt['Prov_Name']); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo htmlspecialchars($apt['Prov_Spec']); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($apt['Status_Descr']) {
                                            case 'Scheduled':
                                            case 'Confirmed':
                                                $statusClass = 'bg-primary';
                                                break;
                                            case 'Completed':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'Cancelled':
                                                $statusClass = 'bg-danger';
                                                break;
                                            case 'Rescheduled':
                                                $statusClass = 'bg-warning';
                                                break;
                                            case 'No Show':
                                                $statusClass = 'bg-dark';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary';
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?> rounded-pill">
                                            <?php echo htmlspecialchars($apt['Status_Descr']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (in_array($apt['Status_Descr'], ['Scheduled', 'Confirmed']) && strtotime($apt['DateTime']) > time()): ?>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        onclick="rescheduleAppointment(<?php echo $apt['Appt_ID']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="cancelAppointment(<?php echo $apt['Appt_ID']; ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No actions available</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No appointments found</h4>
                    <p class="text-muted mb-4">You haven't booked any appointments yet.</p>
                    <a href="book_appointment.php" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-2"></i>Book Your First Appointment
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Cancel Appointment Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this appointment?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="POST" id="cancelForm">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="appointment_id" id="cancelAppointmentId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Appointment</button>
                    <button type="submit" class="btn btn-danger">Cancel Appointment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reschedule Appointment Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reschedule Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="rescheduleForm">
                    <input type="hidden" name="action" value="reschedule">
                    <input type="hidden" name="appointment_id" id="rescheduleAppointmentId">
                    
                    <div class="mb-3">
                        <label for="new_date" class="form-label">New Date</label>
                        <input type="date" class="form-control" id="new_date" name="new_date" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_time" class="form-label">New Time</label>
                        <select class="form-select" id="new_time" name="new_time" required>
                            <option value="">Select new date first</option>
                        </select>
                    </div>
                    
                    <input type="hidden" name="new_datetime" id="new_datetime">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitReschedule()">Reschedule</button>
            </div>
        </div>
    </div>
</div>

<script>
function cancelAppointment(appointmentId) {
    document.getElementById('cancelAppointmentId').value = appointmentId;
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

function rescheduleAppointment(appointmentId) {
    document.getElementById('rescheduleAppointmentId').value = appointmentId;
    new bootstrap.Modal(document.getElementById('rescheduleModal')).show();
}

function submitReschedule() {
    const newDate = document.getElementById('new_date').value;
    const newTime = document.getElementById('new_time').value;
    
    if (newDate && newTime) {
        document.getElementById('new_datetime').value = newDate + ' ' + newTime + ':00';
        document.getElementById('rescheduleForm').submit();
    } else {
        alert('Please select both date and time.');
    }
}

// Load available time slots when date changes
document.getElementById('new_date').addEventListener('change', function() {
    const date = this.value;
    const timeSelect = document.getElementById('new_time');
    
    if (!date) {
        timeSelect.innerHTML = '<option value="">Select new date first</option>';
        return;
    }
    
    // For simplicity, we'll generate basic time slots
    // In a real application, you'd fetch available slots from the server
    const slots = [
        '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
        '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
    ];
    
    timeSelect.innerHTML = '<option value="">Select a time</option>';
    slots.forEach(slot => {
        const option = document.createElement('option');
        option.value = slot;
        option.textContent = formatTime(slot);
        timeSelect.appendChild(option);
    });
});

function formatTime(time) {
    const [hours, minutes] = time.split(':');
    const hour12 = ((parseInt(hours) + 11) % 12 + 1);
    const ampm = parseInt(hours) >= 12 ? 'PM' : 'AM';
    return `${hour12}:${minutes} ${ampm}`;
}
</script>

<?php require_once '../includes/footer.php'; ?>
