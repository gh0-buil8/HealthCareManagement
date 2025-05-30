<?php
$page_title = 'Manage Payments - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('admin');

require_once '../classes/Payment.php';
require_once '../classes/Patient.php';

$payment = new Payment();
$patient = new Patient();

$error_message = '';
$success_message = '';

// Handle payment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $payment_id = $_POST['payment_id'] ?? '';
        $status_id = $_POST['status_id'] ?? '';
        $result = $payment->updatePaymentStatus($payment_id, $status_id);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    } elseif ($action === 'refund') {
        $payment_id = $_POST['payment_id'] ?? '';
        $result = $payment->refundPayment($payment_id);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    } elseif ($action === 'create') {
        $result = $payment->createPayment([
            'patient_id' => $_POST['patient_id'] ?? '',
            'amount' => $_POST['amount'] ?? '',
            'payment_method_id' => $_POST['payment_method_id'] ?? '',
            'payment_status_id' => 1 // Pending
        ]);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$method_filter = $_GET['method'] ?? '';
$patient_search = $_GET['patient_search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build filters array
$filters = [];
if ($status_filter) $filters['status_id'] = $status_filter;
if ($method_filter) $filters['method_id'] = $method_filter;
if ($patient_search) $filters['patient_search'] = $patient_search;
if ($date_from) $filters['date_from'] = $date_from;
if ($date_to) $filters['date_to'] = $date_to;

// Get data
$payments = $payment->getAllPayments(100, 0, $filters);
$paymentMethods = $payment->getPaymentMethods();
$paymentStatuses = $payment->getPaymentStatuses();
$paymentStats = $payment->getPaymentStats($filters);

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Manage Payments</h1>
                    <p class="text-muted mb-0">View and manage payment transactions</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#createPaymentModal">
                        <i class="fas fa-plus me-2"></i>Add Payment
                    </button>
                    <button class="btn btn-primary" onclick="exportPayments()">
                        <i class="fas fa-download me-2"></i>Export Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Stats -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-dollar-sign text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Total Revenue</h6>
                            <h3 class="card-text mb-0">$<?php echo number_format($paymentStats['completed_amount'] ?? 0, 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Pending</h6>
                            <h3 class="card-text mb-0">$<?php echo number_format($paymentStats['pending_amount'] ?? 0, 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Completed</h6>
                            <h3 class="card-text mb-0"><?php echo $paymentStats['completed_payments'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 p-3 rounded">
                                <i class="fas fa-times-circle text-danger fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Failed</h6>
                            <h3 class="card-text mb-0"><?php echo $paymentStats['failed_payments'] ?? 0; ?></h3>
                        </div>
                    </div>
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
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($paymentStatuses as $status): ?>
                            <option value="<?php echo $status['PaymentStat_ID']; ?>" 
                                    <?php echo $status_filter == $status['PaymentStat_ID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($status['Status_Descr']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="method" class="form-label">Method</label>
                    <select class="form-select" id="method" name="method">
                        <option value="">All Methods</option>
                        <?php foreach ($paymentMethods as $method): ?>
                            <option value="<?php echo $method['PaymentMeth_ID']; ?>" 
                                    <?php echo $method_filter == $method['PaymentMeth_ID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($method['MethodName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="patient_search" class="form-label">Patient</label>
                    <input type="text" class="form-control" id="patient_search" name="patient_search" 
                           placeholder="Search patient..." value="<?php echo htmlspecialchars($patient_search); ?>">
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!empty($payments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo str_pad($pay['Payment_ID'], 6, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($pay['Pat_Name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($pay['Pat_Email']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>$<?php echo number_format($pay['Amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary rounded-pill">
                                            <?php echo htmlspecialchars($pay['MethodName']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($pay['Status_Descr']) {
                                            case 'Paid':
                                            case 'Completed':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'Pending':
                                                $statusClass = 'bg-warning';
                                                break;
                                            case 'Failed':
                                            case 'Declined':
                                                $statusClass = 'bg-danger';
                                                break;
                                            case 'Refunded':
                                                $statusClass = 'bg-info';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary';
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?> rounded-pill">
                                            <?php echo htmlspecialchars($pay['Status_Descr']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewPayment(<?php echo $pay['Payment_ID']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($pay['Status_Descr'] === 'Pending'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="markAsPaid(<?php echo $pay['Payment_ID']; ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php elseif ($pay['Status_Descr'] === 'Paid' || $pay['Status_Descr'] === 'Completed'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        onclick="refundPayment(<?php echo $pay['Payment_ID']; ?>)">
                                                    <i class="fas fa-undo"></i>
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
                    <i class="fas fa-credit-card fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No payments found</h4>
                    <?php if (array_filter($filters)): ?>
                        <p class="text-muted mb-4">Try adjusting your filters to see more results.</p>
                        <a href="payments.php" class="btn btn-primary">Clear Filters</a>
                    <?php else: ?>
                        <p class="text-muted mb-4">Payment records will appear here once transactions are processed.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create Payment Modal -->
<div class="modal fade" id="createPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Payment Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label for="patient_id" class="form-label">Patient *</label>
                        <select class="form-select" id="patient_id" name="patient_id" required>
                            <option value="">Select patient</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method_id" class="form-label">Payment Method *</label>
                        <select class="form-select" id="payment_method_id" name="payment_method_id" required>
                            <option value="">Select method</option>
                            <?php foreach ($paymentMethods as $method): ?>
                                <option value="<?php echo $method['PaymentMeth_ID']; ?>">
                                    <?php echo htmlspecialchars($method['MethodName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Payment Modal -->
<div class="modal fade" id="viewPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="paymentDetails">
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
function viewPayment(paymentId) {
    fetch('../api/payments.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_payment_details',
            payment_id: paymentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const payment = data.payment;
            document.getElementById('paymentDetails').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Payment Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Payment ID:</strong></td><td>#${String(payment.Payment_ID).padStart(6, '0')}</td></tr>
                            <tr><td><strong>Amount:</strong></td><td>$${parseFloat(payment.Amount).toFixed(2)}</td></tr>
                            <tr><td><strong>Method:</strong></td><td>${payment.MethodName}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge bg-success">${payment.Status_Descr}</span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Patient Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Name:</strong></td><td>${payment.Pat_Name}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${payment.Pat_Email}</td></tr>
                            <tr><td><strong>Patient ID:</strong></td><td>#${String(payment.Pat_ID).padStart(6, '0')}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('viewPaymentModal')).show();
        } else {
            alert('Error loading payment details');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading payment details');
    });
}

function markAsPaid(paymentId) {
    if (confirm('Mark this payment as paid?')) {
        updatePaymentStatus(paymentId, 2); // 2 = Paid
    }
}

function refundPayment(paymentId) {
    if (confirm('Are you sure you want to refund this payment?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="refund">
            <input type="hidden" name="payment_id" value="${paymentId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function updatePaymentStatus(paymentId, statusId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="payment_id" value="${paymentId}">
        <input type="hidden" name="status_id" value="${statusId}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function exportPayments() {
    const url = new URL('../api/payments.php', window.location.origin);
    url.searchParams.append('action', 'export');
    
    // Add current filters to export
    const urlParams = new URLSearchParams(window.location.search);
    for (const [key, value] of urlParams) {
        url.searchParams.append(key, value);
    }
    
    window.open(url.toString(), '_blank');
}

// Load patients for create modal
document.getElementById('createPaymentModal').addEventListener('show.bs.modal', function() {
    fetch('../api/patients.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'search_patients',
            query: ''
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('patient_id');
            select.innerHTML = '<option value="">Select patient</option>';
            
            data.patients.forEach(patient => {
                const option = document.createElement('option');
                option.value = patient.Pat_ID;
                option.textContent = `${patient.Pat_Name} - ${patient.Pat_Email}`;
                select.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Error loading patients:', error);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
