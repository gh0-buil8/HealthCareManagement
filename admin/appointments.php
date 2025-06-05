<?php
require_once '../config/constants.php';
$page_title = 'Manage Appointments - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('admin');

require_once '../classes/Appointment.php';
require_once '../classes/Patient.php';
require_once '../classes/Provider.php';

$appointment = new Appointment();
$patient = new Patient();
$provider = new Provider();

$error_message = '';
$success_message = '';

// Handle appointment status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $appointment_id = $_POST['appointment_id'] ?? '';
        $status_id = $_POST['status_id'] ?? '';
        $result = $appointment->updateAppointmentStatus($appointment_id, $status_id);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    }
}

// Get filters
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$provider_filter = $_GET['provider'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build filters array
$filters = [];
if ($date_from) $filters['date_from'] = $date_from;
if ($date_to) $filters['date_to'] = $date_to;
if ($provider_filter) $filters['provider_id'] = $provider_filter;
if ($status_filter) $filters['status_id'] = $status_filter;
if ($search) $filters['patient_search'] = $search;

// Get data
$appointments = $appointment->getAllAppointments(100, 0, $filters);
$appointmentStatuses = $appointment->getAppointmentStatuses();
$providers = $provider->getAllProviders(100);

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Manage Appointments</h1>
                    <p class="text-muted mb-0">View and manage all appointments in the system</p>
                </div>
                <div>
                    <a href="reports.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-chart-bar me-2"></i>View Reports
                    </a>
                    <button class="btn btn-primary" onclick="exportAppointments()">
                        <i class="fas fa-download me-2"></i>Export Data
                    </button>
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
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="provider" class="form-label">Provider</label>
                    <select class="form-select" id="provider" name="provider">
                        <option value="">All Providers</option>
                        <?php foreach ($providers as $prov): ?>
                            <option value="<?php echo $prov['Prov_ID']; ?>" 
                                    <?php echo $provider_filter == $prov['Prov_ID'] ? 'selected' : ''; ?>>
                                Dr. <?php echo htmlspecialchars($prov['Prov_Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
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
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="appointments.php" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
            
            <div class="row mt-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search Patient</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Search by patient name or email..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!empty($appointments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Provider</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $apt): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo str_pad($apt['Appt_ID'], 6, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($apt['Pat_Name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($apt['Pat_Email']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>Dr. <?php echo htmlspecialchars($apt['Prov_Name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($apt['Prov_Spec']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo date('M j, Y', strtotime($apt['DateTime'])); ?></strong><br>
                                            <small class="text-muted"><?php echo date('g:i A', strtotime($apt['DateTime'])); ?></small>
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
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewAppointment(<?php echo $apt['Appt_ID']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="editAppointmentStatus(<?php echo $apt['Appt_ID']; ?>, '<?php echo htmlspecialchars($apt['Status_Descr']); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
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
                    <?php if (array_filter($filters)): ?>
                        <p class="text-muted mb-4">Try adjusting your filters to see more results.</p>
                        <a href="appointments.php" class="btn btn-primary">Clear Filters</a>
                    <?php else: ?>
                        <p class="text-muted mb-4">Appointments will appear here once patients start booking.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Appointment Modal -->
<div class="modal fade" id="viewAppointmentModal" tabindex="-1">
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

<!-- Edit Status Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Appointment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="appointment_id" id="edit_appointment_id">
                    
                    <div class="mb-3">
                        <label for="status_id" class="form-label">New Status</label>
                        <select class="form-select" id="status_id" name="status_id" required>
                            <option value="">Select new status</option>
                            <?php foreach ($appointmentStatuses as $status): ?>
                                <option value="<?php echo $status['Status_ID']; ?>">
                                    <?php echo htmlspecialchars($status['Status_Descr']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewAppointment(appointmentId) {
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
                            <tr><td><strong>Provider:</strong></td><td>Dr. ${apt.Prov_Name}</td></tr>
                            <tr><td><strong>Specialty:</strong></td><td>${apt.Prov_Spec}</td></tr>
                            <tr><td><strong>Date:</strong></td><td>${new Date(apt.DateTime).toLocaleDateString()}</td></tr>
                            <tr><td><strong>Time:</strong></td><td>${new Date(apt.DateTime).toLocaleTimeString()}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge bg-primary">${apt.Status_Descr}</span></td></tr>
                            <tr><td><strong>Appointment ID:</strong></td><td>#${String(apt.Appt_ID).padStart(6, '0')}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('viewAppointmentModal')).show();
        } else {
            alert('Error loading appointment details');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading appointment details');
    });
}

function editAppointmentStatus(appointmentId, currentStatus) {
    document.getElementById('edit_appointment_id').value = appointmentId;
    // Set current status as selected
    const statusSelect = document.getElementById('status_id');
    for (let option of statusSelect.options) {
        if (option.text === currentStatus) {
            option.selected = true;
            break;
        }
    }
    
    new bootstrap.Modal(document.getElementById('editStatusModal')).show();
}

function exportAppointments() {
    const url = new URL('../api/appointments.php', window.location.origin);
    url.searchParams.append('action', 'export');
    
    // Add current filters to export
    const urlParams = new URLSearchParams(window.location.search);
    for (const [key, value] of urlParams) {
        url.searchParams.append(key, value);
    }
    
    window.open(url.toString(), '_blank');
}

// Auto-submit form when search changes (with debounce)
let searchTimeout;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        this.form.submit();
    }, 500);
});
</script>

<?php require_once '../includes/footer.php'; ?>
