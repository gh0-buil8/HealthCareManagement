<?php
require_once '../config/constants.php';
$page_title = 'Manage Patients - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('admin');

require_once '../classes/Patient.php';

$patient = new Patient();

$error_message = '';
$success_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $result = $patient->createPatient([
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'dob' => $_POST['dob'] ?? ''
        ]);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    } elseif ($action === 'update') {
        $patient_id = $_POST['patient_id'] ?? '';
        $result = $patient->updatePatient($patient_id, [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'dob' => $_POST['dob'] ?? ''
        ]);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    } elseif ($action === 'delete') {
        $patient_id = $_POST['patient_id'] ?? '';
        $result = $patient->deletePatient($patient_id);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    }
}

// Get filters
$search = $_GET['search'] ?? '';
$limit = 20;
$offset = ($_GET['page'] ?? 1 - 1) * $limit;

// Get patients
$patients = $patient->getAllPatients($limit, $offset, $search);

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Manage Patients</h1>
                    <p class="text-muted mb-0">View and manage patient records</p>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPatientModal">
                        <i class="fas fa-user-plus me-2"></i>Add New Patient
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

    <!-- Search and Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <label for="search" class="form-label">Search Patients</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                    <a href="patients.php" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!empty($patients)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Date of Birth</th>
                                <th>Appointments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $pat): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo str_pad($pat['Pat_ID'], 6, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($pat['Pat_Name']); ?></strong>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($pat['Pat_Email']); ?>">
                                            <?php echo htmlspecialchars($pat['Pat_Email']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($pat['Pat_Phone']): ?>
                                            <a href="tel:<?php echo htmlspecialchars($pat['Pat_Phone']); ?>">
                                                <?php echo htmlspecialchars($pat['Pat_Phone']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($pat['Pat_DOB']): ?>
                                            <?php echo date('M j, Y', strtotime($pat['Pat_DOB'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info rounded-pill">
                                            <?php echo $pat['total_appointments'] ?? 0; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewPatient(<?php echo $pat['Pat_ID']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="editPatient(<?php echo $pat['Pat_ID']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deletePatient(<?php echo $pat['Pat_ID']; ?>)">
                                                <i class="fas fa-trash"></i>
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
                    <i class="fas fa-users fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No patients found</h4>
                    <?php if ($search): ?>
                        <p class="text-muted mb-4">Try adjusting your search criteria.</p>
                        <a href="patients.php" class="btn btn-primary">View All Patients</a>
                    <?php else: ?>
                        <p class="text-muted mb-4">Start by adding your first patient.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPatientModal">
                            <i class="fas fa-user-plus me-2"></i>Add First Patient
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create Patient Modal -->
<div class="modal fade" id="createPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="create_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="create_email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="create_phone" name="phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_dob" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="create_dob" name="dob">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="create_address" class="form-label">Address</label>
                        <textarea class="form-control" id="create_address" name="address" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Patient</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Patient Modal -->
<div class="modal fade" id="editPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="patient_id" id="edit_patient_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_dob" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="edit_dob" name="dob">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Patient</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Patient Modal -->
<div class="modal fade" id="viewPatientModal" tabindex="-1">
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
function viewPatient(patientId) {
    fetch('../api/patients.php', {
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
                        
                        <h6>Statistics</h6>
                        <p>Total Appointments: <span class="badge bg-info">${data.stats.total_appointments}</span></p>
                        <p>Completed: <span class="badge bg-success">${data.stats.completed_appointments}</span></p>
                        <p>Total Paid: <span class="badge bg-primary">$${data.stats.total_payments}</span></p>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('viewPatientModal')).show();
        } else {
            alert('Error loading patient details');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading patient details');
    });
}

function editPatient(patientId) {
    fetch('../api/patients.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_patient',
            patient_id: patientId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const patient = data.patient;
            document.getElementById('edit_patient_id').value = patient.Pat_ID;
            document.getElementById('edit_name').value = patient.Pat_Name;
            document.getElementById('edit_email').value = patient.Pat_Email;
            document.getElementById('edit_phone').value = patient.Pat_Phone || '';
            document.getElementById('edit_dob').value = patient.Pat_DOB || '';
            document.getElementById('edit_address').value = patient.Pat_Addr || '';
            
            new bootstrap.Modal(document.getElementById('editPatientModal')).show();
        } else {
            alert('Error loading patient data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading patient data');
    });
}

function deletePatient(patientId) {
    if (confirm('Are you sure you want to delete this patient? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="patient_id" value="${patientId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
