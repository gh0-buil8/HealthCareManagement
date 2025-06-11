<?php
require_once '../config/config.php';
$page_title = 'Manage Providers - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('admin');

require_once '../classes/Provider.php';

$provider = new Provider();

$error_message = '';
$success_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $result = $provider->createProvider([
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'specialty' => $_POST['specialty'] ?? ''
        ]);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    } elseif ($action === 'update') {
        $provider_id = $_POST['provider_id'] ?? '';
        $result = $provider->updateProvider($provider_id, [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'specialty' => $_POST['specialty'] ?? ''
        ]);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    } elseif ($action === 'delete') {
        $provider_id = $_POST['provider_id'] ?? '';
        $result = $provider->deleteProvider($provider_id);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    }
}

// Get filters
$search = $_GET['search'] ?? '';
$specialty_filter = $_GET['specialty'] ?? '';
$limit = 20;
$offset = ($_GET['page'] ?? 1 - 1) * $limit;

// Get providers
$providers = $provider->getAllProviders($limit, $offset, $search);
$specialties = $provider->getSpecialties();

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Manage Healthcare Providers</h1>
                    <p class="text-muted mb-0">View and manage healthcare provider records</p>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProviderModal">
                        <i class="fas fa-user-md me-2"></i>Add New Provider
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
                <div class="col-md-6">
                    <label for="search" class="form-label">Search Providers</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Search by name or specialty..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-4">
                    <label for="specialty" class="form-label">Specialty</label>
                    <select class="form-select" id="specialty" name="specialty">
                        <option value="">All Specialties</option>
                        <?php foreach ($specialties as $spec): ?>
                            <option value="<?php echo htmlspecialchars($spec['Prov_Spec']); ?>" 
                                    <?php echo $specialty_filter === $spec['Prov_Spec'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($spec['Prov_Spec']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                    <a href="providers.php" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Providers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!empty($providers)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Specialty</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Appointments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($providers as $prov): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo str_pad($prov['Prov_ID'], 6, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
                                    <td>
                                        <strong>Dr. <?php echo htmlspecialchars($prov['Prov_Name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info rounded-pill">
                                            <?php echo htmlspecialchars($prov['Prov_Spec']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($prov['Prov_Email']); ?>">
                                            <?php echo htmlspecialchars($prov['Prov_Email']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($prov['Prov_Phone']): ?>
                                            <a href="tel:<?php echo htmlspecialchars($prov['Prov_Phone']); ?>">
                                                <?php echo htmlspecialchars($prov['Prov_Phone']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill">
                                            <?php echo $prov['total_appointments'] ?? 0; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewProvider(<?php echo $prov['Prov_ID']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="editProvider(<?php echo $prov['Prov_ID']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteProvider(<?php echo $prov['Prov_ID']; ?>)">
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
                    <i class="fas fa-user-md fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No providers found</h4>
                    <?php if ($search || $specialty_filter): ?>
                        <p class="text-muted mb-4">Try adjusting your search criteria.</p>
                        <a href="providers.php" class="btn btn-primary">View All Providers</a>
                    <?php else: ?>
                        <p class="text-muted mb-4">Start by adding your first healthcare provider.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProviderModal">
                            <i class="fas fa-user-md me-2"></i>Add First Provider
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create Provider Modal -->
<div class="modal fade" id="createProviderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Healthcare Provider</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="create_name" name="name" 
                                   placeholder="e.g., John Smith" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_specialty" class="form-label">Specialty *</label>
                            <input type="text" class="form-control" id="create_specialty" name="specialty" 
                                   placeholder="e.g., Cardiologist" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="create_email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="create_phone" name="phone">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Provider</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Provider Modal -->
<div class="modal fade" id="editProviderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Healthcare Provider</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="provider_id" id="edit_provider_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_specialty" class="form-label">Specialty *</label>
                            <input type="text" class="form-control" id="edit_specialty" name="specialty" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Provider</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Provider Modal -->
<div class="modal fade" id="viewProviderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Provider Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="providerDetails">
                    <!-- Provider details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewProvider(providerId) {
    fetch('../api/providers.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_provider_details',
            provider_id: providerId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const provider = data.provider;
            document.getElementById('providerDetails').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Provider Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Name:</strong></td><td>Dr. ${provider.Prov_Name}</td></tr>
                            <tr><td><strong>Specialty:</strong></td><td>${provider.Prov_Spec}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${provider.Prov_Email}</td></tr>
                            <tr><td><strong>Phone:</strong></td><td>${provider.Prov_Phone || 'N/A'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Availability</h6>
                        <p>${data.availability || 'No availability set'}</p>
                        
                        <h6>Statistics</h6>
                        <p>Total Appointments: <span class="badge bg-info">${data.stats.total_appointments}</span></p>
                        <p>Today's Appointments: <span class="badge bg-primary">${data.stats.todays_appointments}</span></p>
                        <p>Completed: <span class="badge bg-success">${data.stats.completed_appointments}</span></p>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('viewProviderModal')).show();
        } else {
            alert('Error loading provider details');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading provider details');
    });
}

function editProvider(providerId) {
    fetch('../api/providers.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_provider',
            provider_id: providerId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const provider = data.provider;
            document.getElementById('edit_provider_id').value = provider.Prov_ID;
            document.getElementById('edit_name').value = provider.Prov_Name;
            document.getElementById('edit_specialty').value = provider.Prov_Spec;
            document.getElementById('edit_email').value = provider.Prov_Email;
            document.getElementById('edit_phone').value = provider.Prov_Phone || '';
            
            new bootstrap.Modal(document.getElementById('editProviderModal')).show();
        } else {
            alert('Error loading provider data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading provider data');
    });
}

function deleteProvider(providerId) {
    if (confirm('Are you sure you want to delete this provider? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="provider_id" value="${providerId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
