<?php
$page_title = 'Appointments - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('provider');

require_once '../classes/Appointment.php';

$user = getCurrentUser();
$appointment = new Appointment();

$error_message = '';
$success_message = '';

// Handle filter parameters
$date_filter = $_GET['date'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Handle appointment status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $appointment_id = $_POST['appointment_id'] ?? '';
    
    if ($action === 'update_status' && $appointment_id) {
        $status_id = $_POST['status_id'] ?? '';
        $result = $appointment->updateAppointmentStatus($appointment_id, $status_id);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    }
}

// Build filters for query
$filters = [
    'provider_id' => $user['id']
];

if ($date_filter) {
    $filters['date_from'] = $date_filter;
    $filters['date_to'] = $date_filter;
}

if ($status_filter) {
    $filters['status_id'] = $status_filter;
}

if ($search) {
    $filters['patient_search'] = $search;
}

// Get appointments
$appointments = $appointment->getAllAppointments(100, 0, $filters);
$appointmentStatuses = $appointment->getAppointmentStatuses();

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">My Appointments</h1>
                    <p class="text-muted mb-0">Manage your patient appointments</p>
                </div>
                <div>
                    <a href="schedule.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-calendar-alt me-2"></i>Calendar View
                    </a>
                    <a href="availability.php" class="btn btn-primary">
                        <i class="fas fa-clock me-2"></i>Availability
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

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($appointmentStatuses as $status): ?>
                            <option value="<?php echo $status['Status_ID']; ?>" 
                                    <?php echo $status_filter == $status['Status_ID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($status['Status_Descr']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="search" class="form-label">Search Patient</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Patient name or email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="appointments.php" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Appointments List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!empty($appointments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Patient</th>
                                <th>Contact</th>
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
                                            <strong><?php echo htmlspecialchars($apt['Pat_Name']); ?></strong><br>
                                            <small class="text-muted">ID: #<?php echo str_pad($apt['Pat_ID'], 6, '0', STR_PAD_LEFT); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <small class="d-block">
                                                <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($apt['Pat_Email']); ?>
                                            </small>
                                            <?php if ($apt['Pat_Phone']): ?>
                                                <small class="d-block">
                                                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($apt['Pat_Phone']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
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
                                            case 'Checked In':
                                                $statusClass = 'bg-info';
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
                                        <div class="btn-group" role="group">
                                            <?php if ($apt['Status_Descr'] === 'Scheduled' || $apt['Status_Descr'] === 'Confirmed'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="markCheckedIn(<?php echo $apt['Appt_ID']; ?>)" 
                                                        title="Check In">
                                                    <i class="fas fa-sign-in-alt"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="markCompleted(<?php echo $apt['Appt_ID']; ?>)" 
                                                        title="Mark Complete">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php elseif ($apt['Status_Descr'] === 'Checked In'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="markCompleted(<?php echo $apt['Appt_ID']; ?>)" 
                                                        title="Mark Complete">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="viewDetails(<?php echo $apt['Appt_ID']; ?>)" 
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <?php if ($apt['Status_Descr'] !== 'Completed' && $apt['Status_Descr'] !== 'Cancelled'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        onclick="markNoShow(<?php echo $apt['Appt_ID']; ?>)" 
                                                        title="Mark No Show">
                                                    <i class="fas fa-user-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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
                    <?php if ($date_filter || $status_filter || $search): ?>
                        <p class="text-muted mb-4">Try adjusting your filters to see more results.</p>
                        <a href="appointments.php" class="btn btn-primary">Clear Filters</a>
                    <?php else: ?>
                        <p class="text-muted mb-4">Your appointments will appear here once patients book with you.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="appointmentDetails">
                    <!-- Details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function markCheckedIn(appointmentId) {
    updateAppointmentStatus(appointmentId, 6, 'check in'); // 6 = Checked In
}

function markCompleted(appointmentId) {
    updateAppointmentStatus(appointmentId, 2, 'mark as completed'); // 2 = Completed
}

function markNoShow(appointmentId) {
    updateAppointmentStatus(appointmentId, 5, 'mark as no show'); // 5 = No Show
}

function updateAppointmentStatus(appointmentId, statusId, action) {
    if (confirm(`Are you sure you want to ${action} this appointment?`)) {
        fetch('../api/appointments.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_status',
                appointment_id: appointmentId,
                status_id: statusId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating appointment status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating appointment status');
        });
    }
}

function viewDetails(appointmentId) {
    fetch('../api/appointments.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_appointment_details',
            appointment_id: appointmentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const apt = data.appointment;
            document.getElementById('appointmentDetails').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Patient Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Name:</strong></td><td>${apt.Pat_Name}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${apt.Pat_Email}</td></tr>
                            <tr><td><strong>Phone:</strong></td><td>${apt.Pat_Phone || 'N/A'}</td></tr>
                            <tr><td><strong>Patient ID:</strong></td><td>#${String(apt.Pat_ID).padStart(6, '0')}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Appointment Details</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Date:</strong></td><td>${new Date(apt.DateTime).toLocaleDateString()}</td></tr>
                            <tr><td><strong>Time:</strong></td><td>${new Date(apt.DateTime).toLocaleTimeString()}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge bg-primary">${apt.Status_Descr}</span></td></tr>
                            <tr><td><strong>Appointment ID:</strong></td><td>#${String(apt.Appt_ID).padStart(6, '0')}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('appointmentModal')).show();
        } else {
            alert('Error loading appointment details');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading appointment details');
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
