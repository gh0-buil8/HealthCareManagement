<?php
require_once '../config/constants.php';
$page_title = 'Admin Dashboard - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('admin');

require_once '../classes/Patient.php';
require_once '../classes/Provider.php';
require_once '../classes/Appointment.php';
require_once '../classes/Payment.php';
require_once '../classes/Notification.php';

$user = getCurrentUser();
$patient = new Patient();
$provider = new Provider();
$appointment = new Appointment();
$payment = new Payment();
$notification = new Notification();

// Get dashboard statistics
$totalPatients = count($patient->getAllPatients(1000));
$totalProviders = count($provider->getAllProviders(1000));
$totalAppointments = count($appointment->getAllAppointments(1000));
$recentAppointments = $appointment->getAllAppointments(5);
$paymentStats = $payment->getPaymentStats();
$notificationStats = $notification->getNotificationStats();

// Get monthly data for charts
$monthlyPayments = $payment->getMonthlyPaymentStats();

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Admin Dashboard</h1>
                    <p class="text-muted mb-0">Healthcare Appointment Management System Overview</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                    <a href="reports.php" class="btn btn-primary">
                        <i class="fas fa-chart-bar me-2"></i>View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-users text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Total Patients</h6>
                            <h3 class="card-text mb-0"><?php echo number_format($totalPatients); ?></h3>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>Active Users
                            </small>
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
                                <i class="fas fa-user-md text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Healthcare Providers</h6>
                            <h3 class="card-text mb-0"><?php echo number_format($totalProviders); ?></h3>
                            <small class="text-info">
                                <i class="fas fa-stethoscope me-1"></i>Medical Staff
                            </small>
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
                                <i class="fas fa-calendar-check text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Total Appointments</h6>
                            <h3 class="card-text mb-0"><?php echo number_format($totalAppointments); ?></h3>
                            <small class="text-primary">
                                <i class="fas fa-clock me-1"></i>All Time
                            </small>
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
                                <i class="fas fa-dollar-sign text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Total Revenue</h6>
                            <h3 class="card-text mb-0">$<?php echo number_format($paymentStats['completed_amount'] ?? 0, 2); ?></h3>
                            <small class="text-success">
                                <i class="fas fa-chart-line me-1"></i>Completed Payments
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Monthly Revenue Trend
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2 text-success"></i>Payment Status
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Quick Actions -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock me-2 text-info"></i>Recent Appointments
                    </h5>
                    <a href="appointments.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentAppointments)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient</th>
                                        <th>Provider</th>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentAppointments as $apt): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($apt['Pat_Name']); ?></td>
                                            <td>Dr. <?php echo htmlspecialchars($apt['Prov_Name']); ?></td>
                                            <td>
                                                <small>
                                                    <?php echo date('M j, Y g:i A', strtotime($apt['DateTime'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary rounded-pill">
                                                    <?php echo htmlspecialchars($apt['Status_Descr']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent appointments</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

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
                        <a href="patients.php" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus me-2"></i>Manage Patients
                        </a>
                        <a href="providers.php" class="btn btn-outline-success">
                            <i class="fas fa-user-md me-2"></i>Manage Providers
                        </a>
                        <a href="appointments.php" class="btn btn-outline-info">
                            <i class="fas fa-calendar-check me-2"></i>View Appointments
                        </a>
                        <a href="payments.php" class="btn btn-outline-warning">
                            <i class="fas fa-credit-card me-2"></i>Payment Management
                        </a>
                        <a href="notifications.php" class="btn btn-outline-secondary">
                            <i class="fas fa-bell me-2"></i>Send Notifications
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Stats -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2 text-secondary"></i>System Stats
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-primary mb-0"><?php echo $paymentStats['pending_payments'] ?? 0; ?></h4>
                                <small class="text-muted">Pending Payments</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-success mb-0"><?php echo $paymentStats['completed_payments'] ?? 0; ?></h4>
                                <small class="text-muted">Completed Payments</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-info mb-0"><?php echo $notificationStats['recent_notifications'] ?? 0; ?></h4>
                                <small class="text-muted">Recent Notifications</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-warning mb-0"><?php echo $notificationStats['total_notifications'] ?? 0; ?></h4>
                                <small class="text-muted">Total Notifications</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: [
            <?php 
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            for ($i = 1; $i <= 12; $i++) {
                echo "'" . $months[$i-1] . "',";
            }
            ?>
        ],
        datasets: [{
            label: 'Revenue',
            data: [
                <?php 
                for ($i = 1; $i <= 12; $i++) {
                    $amount = 0;
                    foreach ($monthlyPayments as $payment) {
                        if ($payment['month'] == $i) {
                            $amount = $payment['total_amount'];
                            break;
                        }
                    }
                    echo $amount . ',';
                }
                ?>
            ],
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Payment Status Chart
const paymentCtx = document.getElementById('paymentStatusChart').getContext('2d');
const paymentChart = new Chart(paymentCtx, {
    type: 'doughnut',
    data: {
        labels: ['Completed', 'Pending', 'Failed'],
        datasets: [{
            data: [
                <?php echo $paymentStats['completed_payments'] ?? 0; ?>,
                <?php echo $paymentStats['pending_payments'] ?? 0; ?>,
                <?php echo $paymentStats['failed_payments'] ?? 0; ?>
            ],
            backgroundColor: ['#198754', '#ffc107', '#dc3545'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function refreshDashboard() {
    location.reload();
}
</script>

<?php require_once '../includes/footer.php'; ?>
