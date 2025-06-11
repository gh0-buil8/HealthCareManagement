<?php
require_once '../config/config.php';
$page_title = 'Schedule - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('provider');

require_once '../classes/Appointment.php';

$user = getCurrentUser();
$appointment = new Appointment();

// Get current week's appointments
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

$weeklyAppointments = [];
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime($startOfWeek . " +{$i} days"));
    $weeklyAppointments[$date] = $appointment->getProviderAppointments($user['user_id'], $date, 50);
}

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Weekly Schedule</h1>
                    <p class="text-muted mb-0">Week of <?php echo date('M j', strtotime($startOfWeek)); ?> - <?php echo date('M j, Y', strtotime($endOfWeek)); ?></p>
                </div>
                <div>
                    <a href="availability.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-clock me-2"></i>Update Availability
                    </a>
                    <a href="appointments.php" class="btn btn-primary">
                        <i class="fas fa-list me-2"></i>List View
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Week Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="?week=<?php echo date('Y-m-d', strtotime($startOfWeek . ' -7 days')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-chevron-left me-2"></i>Previous Week
                        </a>
                        
                        <h5 class="mb-0">
                            <?php echo date('F j', strtotime($startOfWeek)) . ' - ' . date('F j, Y', strtotime($endOfWeek)); ?>
                        </h5>
                        
                        <a href="?week=<?php echo date('Y-m-d', strtotime($startOfWeek . ' +7 days')); ?>" class="btn btn-outline-secondary">
                            Next Week<i class="fas fa-chevron-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Grid -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered schedule-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Time</th>
                                    <?php for ($i = 0; $i < 7; $i++): ?>
                                        <?php $date = date('Y-m-d', strtotime($startOfWeek . " +{$i} days")); ?>
                                        <th class="text-center">
                                            <div><?php echo date('D', strtotime($date)); ?></div>
                                            <div class="small text-muted"><?php echo date('M j', strtotime($date)); ?></div>
                                        </th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($hour = 8; $hour < 18; $hour++): ?>
                                    <tr>
                                        <td class="text-center align-middle bg-light">
                                            <strong><?php echo date('g:i A', strtotime($hour . ':00')); ?></strong>
                                        </td>
                                        <?php for ($i = 0; $i < 7; $i++): ?>
                                            <?php 
                                            $date = date('Y-m-d', strtotime($startOfWeek . " +{$i} days"));
                                            $dayAppointments = $weeklyAppointments[$date] ?? [];
                                            
                                            // Find appointments for this hour
                                            $hourAppointments = array_filter($dayAppointments, function($apt) use ($hour) {
                                                return date('G', strtotime($apt['DateTime'])) == $hour;
                                            });
                                            ?>
                                            <td style="height: 80px; vertical-align: top; position: relative;">
                                                <?php if (!empty($hourAppointments)): ?>
                                                    <?php foreach ($hourAppointments as $apt): ?>
                                                        <div class="appointment-slot mb-1" 
                                                             style="background: <?php echo getStatusColor($apt['Status_Descr']); ?>; border-radius: 4px; padding: 4px; font-size: 11px; cursor: pointer;"
                                                             onclick="viewAppointmentDetails(<?php echo $apt['Appt_ID']; ?>)">
                                                            <div class="text-white">
                                                                <strong><?php echo date('g:i A', strtotime($apt['DateTime'])); ?></strong><br>
                                                                <?php echo htmlspecialchars($apt['Pat_Name']); ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div class="text-center text-muted small" style="padding-top: 20px;">
                                                        Available
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Status Legend</h6>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <div class="status-indicator bg-primary me-2"></div>
                            <small>Scheduled</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="status-indicator bg-success me-2"></div>
                            <small>Completed</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="status-indicator bg-warning me-2"></div>
                            <small>Confirmed</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="status-indicator bg-danger me-2"></div>
                            <small>Cancelled</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="status-indicator bg-info me-2"></div>
                            <small>Checked In</small>
                        </div>
                    </div>
                </div>
            </div>
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
                    <!-- Appointment details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="markCompleteBtn" style="display: none;">
                    Mark as Complete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.schedule-table {
    font-size: 12px;
}

.schedule-table th, .schedule-table td {
    border: 1px solid #dee2e6;
    padding: 8px;
}

.status-indicator {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: inline-block;
}

.appointment-slot:hover {
    opacity: 0.8;
    transform: scale(1.02);
    transition: all 0.2s;
}
</style>

<script>
function viewAppointmentDetails(appointmentId) {
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
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Appointment Details</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Date:</strong></td><td>${new Date(apt.DateTime).toLocaleDateString()}</td></tr>
                            <tr><td><strong>Time:</strong></td><td>${new Date(apt.DateTime).toLocaleTimeString()}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge bg-primary">${apt.Status_Descr}</span></td></tr>
                        </table>
                    </div>
                </div>
            `;
            
            // Show mark complete button if appointment is not completed
            const markCompleteBtn = document.getElementById('markCompleteBtn');
            if (apt.Status_Descr !== 'Completed') {
                markCompleteBtn.style.display = 'block';
                markCompleteBtn.onclick = () => markAppointmentComplete(appointmentId);
            } else {
                markCompleteBtn.style.display = 'none';
            }
            
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

function markAppointmentComplete(appointmentId) {
    if (confirm('Mark this appointment as complete?')) {
        fetch('../api/appointments.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_status',
                appointment_id: appointmentId,
                status_id: 2 // Completed status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('appointmentModal')).hide();
                location.reload();
            } else {
                alert('Error updating appointment: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating appointment');
        });
    }
}
</script>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'Scheduled':
            return '#0d6efd'; // Blue
        case 'Completed':
            return '#198754'; // Green
        case 'Confirmed':
            return '#ffc107'; // Yellow
        case 'Cancelled':
            return '#dc3545'; // Red
        case 'Checked In':
            return '#17a2b8'; // Cyan
        default:
            return '#6c757d'; // Gray
    }
}

require_once '../includes/footer.php';
?>
