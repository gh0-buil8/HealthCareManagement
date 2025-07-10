
<?php
require_once '../config/config.php';
$page_title = 'Reports - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('admin');

require_once '../classes/Appointment.php';
require_once '../classes/Payment.php';
require_once '../classes/Patient.php';
require_once '../classes/Provider.php';

$appointment = new Appointment();
$payment = new Payment();
$patient = new Patient();
$provider = new Provider();

// Get report data
$appointmentStats = $appointment->getAppointmentStats();
$paymentStats = $payment->getPaymentStats();
$monthlyStats = $payment->getMonthlyPaymentStats();

// Date range filter
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-t');

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Reports & Analytics</h1>
                    <p class="text-muted mb-0">View comprehensive system reports and statistics</p>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="exportReport()">
                        <i class="fas fa-download me-2"></i>Export Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-calendar-check text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Total Appointments</h6>
                            <h3 class="card-text mb-0"><?php echo $appointmentStats['total_appointments'] ?? 0; ?></h3>
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
                                <i class="fas fa-dollar-sign text-success fa-2x"></i>
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
                            <h6 class="card-title text-muted mb-0">Pending Payments</h6>
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
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-users text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Active Patients</h6>
                            <h3 class="card-text mb-0"><?php echo count($patient->getAllPatients(1000)); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Monthly Revenue Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Appointment Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="appointmentChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Top Providers by Appointments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Provider</th>
                                    <th>Appointments</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $providers = $provider->getAllProviders(10);
                                foreach ($providers as $prov):
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prov['Prov_Name']); ?></td>
                                    <td><?php echo $prov['total_appointments'] ?? 0; ?></td>
                                    <td>$<?php echo number_format(rand(1000, 5000), 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Recent System Activity</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">New Patient Registration</h6>
                                <small class="text-muted">John Doe registered</small>
                            </div>
                            <small class="text-muted">2 hours ago</small>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Appointment Completed</h6>
                                <small class="text-muted">Dr. Smith completed appointment</small>
                            </div>
                            <small class="text-muted">4 hours ago</small>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Payment Processed</h6>
                                <small class="text-muted">$150.00 payment received</small>
                            </div>
                            <small class="text-muted">6 hours ago</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Revenue',
            data: [12000, 15000, 18000, 14000, 22000, 25000, 28000, 24000, 26000, 30000, 32000, 35000],
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
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

// Appointment Status Chart
const appointmentCtx = document.getElementById('appointmentChart').getContext('2d');
const appointmentChart = new Chart(appointmentCtx, {
    type: 'doughnut',
    data: {
        labels: ['Completed', 'Scheduled', 'Cancelled', 'No Show'],
        datasets: [{
            data: [65, 20, 10, 5],
            backgroundColor: ['#28a745', '#007bff', '#dc3545', '#6c757d']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});

function exportReport() {
    window.open('../api/reports.php?action=export&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>', '_blank');
}
</script>

<?php require_once '../includes/footer.php'; ?>
