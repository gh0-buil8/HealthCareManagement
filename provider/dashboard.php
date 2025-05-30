<?php
$page_title = 'Provider Dashboard - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('provider');

require_once '../classes/Appointment.php';
require_once '../classes/Provider.php';

$user = getCurrentUser();
$appointment = new Appointment();
$provider = new Provider();

// Get dashboard data
$todaysAppointments = $appointment->getProviderAppointments($user['id'], date('Y-m-d'), 20);
$upcomingAppointments = $appointment->getProviderAppointments($user['id'], null, 10);
$providerStats = $provider->getProviderStats($user['id']);

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Welcome back, Dr. <?php echo htmlspecialchars($user['name']); ?>!</h1>
                    <p class="text-muted mb-0">Manage your appointments and patient care</p>
                </div>
                <div>
                    <a href="availability.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-clock me-2"></i>Update Availability
                    </a>
                    <a href="appointments.php" class="btn btn-primary">
                        <i class="fas fa-calendar me-2"></i>View All Appointments
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-calendar-day text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Today's Appointments</h6>
                            <h3 class="card-text mb-0"><?php echo $providerStats['todays_appointments']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Completed</h6>
                            <h3 class="card-text mb-0"><?php echo $providerStats['completed_appointments']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Pending</h6>
                            <h3 class="card-text mb-0"><?php echo $providerStats['pending_appointments']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-calendar-check text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Total Appointments</h6>
                            <h3 class="card-text mb-0"><?php echo $providerStats['total_appointments']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Today's Schedule -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-day me-2 text-primary"></i>Today's Schedule
                        <small class="text-muted ms-2"><?php echo date('F j, Y'); ?></small>
                    </h5>
                    <a href="appointments.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($todaysAppointments)): ?>
                        <div class="timeline">
                            <?php foreach ($todaysAppointments as $apt): ?>
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 text-center" style="width: 80px;">
                                            <div class="bg-primary text-white rounded px-2 py-1 small">
                                                <?php echo date('g:i A', strtotime($apt['DateTime'])); ?>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="card border">
                                                <div class="card-body py-2">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($apt['Pat_Name']); ?></h6>
                                                            <p class="mb-1 text-muted small">
                                                                <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($apt['Pat_Email']); ?>
                                                            </p>
                                                            <p class="mb-0 text-muted small">
                                                                <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($apt['Pat_Phone']); ?>
                                                            </p>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge bg-<?php echo $apt['Status_Descr'] === 'Scheduled' ? 'primary' : 'success'; ?> rounded-pill mb-2">
                                                                <?php echo htmlspecialchars($apt['Status_Descr']); ?>
                                                            </span>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <button type="button" class="btn btn-outline-success btn-sm" 
                                                                        onclick="updateAppointmentStatus(<?php echo $apt['Appt_ID']; ?>, 'checked_in')">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-outline-info btn-sm" 
                                                                        onclick="viewPatientDetails(<?php echo $apt['Pat_ID']; ?>)">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No appointments scheduled for today</h6>
                            <p class="text-muted">Enjoy your free day!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Upcoming -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="appointments.php" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-check me-2"></i>View All Appointments
                        </a>
                        <a href="schedule.php" class="btn btn-outline-info">
                            <i class="fas fa-calendar-alt me-2"></i>Manage Schedule
                        </a>
                        <a href="availability.php" class="btn btn-outline-success">
                            <i class="fas fa-clock me-2"></i>Update Availability
                        </a>
                        <a href="profile.php" class="btn btn-outline-secondary">
                            <i class="fas fa-user-edit me-2"></i>Update Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock me-2 text-info"></i>Upcoming This Week
                    </h5>
                </div>
                <div class="card-body">
                    <?php 
                    $weeklyAppointments = array_filter($upcomingAppointments, function($apt) {
                        return strtotime($apt['DateTime']) > time() && strtotime($apt['DateTime']) < strtotime('+7 days');
                    });
                    ?>
                    
                    <?php if (!empty($weeklyAppointments)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($weeklyAppointments, 0, 5) as $apt): ?>
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($apt['Pat_Name']); ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('M j, g:i A', strtotime($apt['DateTime'])); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">
                                            <?php echo htmlspecialchars($apt['Status_Descr']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-calendar-check fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No upcoming appointments this week</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Patient Details Modal -->
<div class="modal fade" id="patientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Patient Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="patientDetails">
                    <!-- Patient details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function updateAppointmentStatus(appointmentId, status) {
    if (confirm('Are you sure you want to update this appointment status?')) {
        fetch('../api/appointments.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_status',
                appointment_id: appointmentId,
                status: status
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

function viewPatientDetails(patientId) {
    fetch('../api/appointments.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_patient_details',
            patient_id: patientId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const patient = data.patient;
            document.getElementById('patientDetails').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Personal Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Name:</strong></td><td>${patient.Pat_Name}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${patient.Pat_Email}</td></tr>
                            <tr><td><strong>Phone:</strong></td><td>${patient.Pat_Phone || 'N/A'}</td></tr>
                            <tr><td><strong>DOB:</strong></td><td>${patient.Pat_DOB || 'N/A'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Address</h6>
                        <p>${patient.Pat_Addr || 'No address on file'}</p>
                        
                        <h6>Patient ID</h6>
                        <p>#${String(patient.Pat_ID).padStart(6, '0')}</p>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('patientModal')).show();
        } else {
            alert('Error loading patient details');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading patient details');
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
