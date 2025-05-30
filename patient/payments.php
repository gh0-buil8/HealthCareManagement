<?php
$page_title = 'My Payments - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('patient');

require_once '../classes/Payment.php';

$user = getCurrentUser();
$payment = new Payment();

$error_message = '';
$success_message = '';

// Get payments
$payments = $payment->getPatientPayments($user['id'], 50);
$paymentMethods = $payment->getPaymentMethods();

// Calculate totals
$totalPaid = 0;
$totalPending = 0;
$totalOverdue = 0;

foreach ($payments as $pay) {
    switch ($pay['Status_Descr']) {
        case 'Paid':
        case 'Completed':
            $totalPaid += $pay['Amount'];
            break;
        case 'Pending':
            $totalPending += $pay['Amount'];
            break;
        case 'Overdue':
            $totalOverdue += $pay['Amount'];
            break;
    }
}

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">My Payments</h1>
                    <p class="text-muted mb-0">View and manage your payment history</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Total Paid</h6>
                            <h3 class="card-text mb-0 text-success">$<?php echo number_format($totalPaid, 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
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
                            <h3 class="card-text mb-0 text-warning">$<?php echo number_format($totalPending, 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 p-3 rounded">
                                <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Overdue</h6>
                            <h3 class="card-text mb-0 text-danger">$<?php echo number_format($totalOverdue, 2); ?></h3>
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

    <!-- Payment History -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0">
            <h5 class="card-title mb-0">
                <i class="fas fa-history me-2 text-primary"></i>Payment History
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($payments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Payment ID</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
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
                                        <strong>$<?php echo number_format($pay['Amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo htmlspecialchars($pay['MethodName']); ?></span>
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
                                        <?php if ($pay['Status_Descr'] === 'Pending'): ?>
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    onclick="payNow(<?php echo $pay['Payment_ID']; ?>, <?php echo $pay['Amount']; ?>)">
                                                <i class="fas fa-credit-card me-1"></i>Pay Now
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="viewDetails(<?php echo $pay['Payment_ID']; ?>)">
                                                <i class="fas fa-eye me-1"></i>View
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No payment history found</h4>
                    <p class="text-muted mb-4">Your payment records will appear here once you start using our services.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="payment_id" name="payment_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" readonly>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select payment method</option>
                                <?php foreach ($paymentMethods as $method): ?>
                                    <option value="<?php echo $method['PaymentMeth_ID']; ?>">
                                        <?php echo htmlspecialchars($method['MethodName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="card_number" class="form-label">Card Number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" 
                                   placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="expiry" class="form-label">Expiry Date</label>
                            <input type="text" class="form-control" id="expiry" name="expiry" 
                                   placeholder="MM/YY" maxlength="5">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cvv" name="cvv" 
                                   placeholder="123" maxlength="4">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="cardholder_name" class="form-label">Cardholder Name</label>
                        <input type="text" class="form-control" id="cardholder_name" name="cardholder_name" 
                               placeholder="John Doe">
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-shield-alt me-2"></i>
                        Your payment information is encrypted and secure.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="processPayment()">
                    <i class="fas fa-credit-card me-2"></i>Process Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="paymentDetails">
                    <!-- Payment details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function payNow(paymentId, amount) {
    document.getElementById('payment_id').value = paymentId;
    document.getElementById('amount').value = amount;
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

function processPayment() {
    const form = document.getElementById('paymentForm');
    const formData = new FormData(form);
    
    // Basic validation
    if (!formData.get('payment_method')) {
        alert('Please select a payment method.');
        return;
    }
    
    // In a real application, you would process the payment through a payment gateway
    // For now, we'll just show a success message
    
    fetch('../api/payments.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'process_payment',
            payment_id: formData.get('payment_id'),
            payment_method: formData.get('payment_method'),
            card_details: {
                number: formData.get('card_number'),
                expiry: formData.get('expiry'),
                cvv: formData.get('cvv'),
                name: formData.get('cardholder_name')
            }
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Payment processed successfully!');
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            location.reload();
        } else {
            alert('Payment failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Payment processing error. Please try again.');
    });
}

function viewDetails(paymentId) {
    // Load payment details
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
            document.getElementById('paymentDetails').innerHTML = `
                <div class="row">
                    <div class="col-sm-4"><strong>Payment ID:</strong></div>
                    <div class="col-sm-8">#${String(data.payment.Payment_ID).padStart(6, '0')}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Amount:</strong></div>
                    <div class="col-sm-8">$${parseFloat(data.payment.Amount).toFixed(2)}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Method:</strong></div>
                    <div class="col-sm-8">${data.payment.MethodName}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Status:</strong></div>
                    <div class="col-sm-8"><span class="badge bg-success">${data.payment.Status_Descr}</span></div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('detailsModal')).show();
        } else {
            alert('Error loading payment details.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading payment details.');
    });
}

// Format card number input
document.getElementById('card_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    if (formattedValue.length > 19) formattedValue = formattedValue.substr(0, 19);
    e.target.value = formattedValue;
});

// Format expiry date input
document.getElementById('expiry').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value;
});

// Format CVV input
document.getElementById('cvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
});
</script>

<?php require_once '../includes/footer.php'; ?>
