<?php
require_once '../config/constants.php';
$page_title = 'Patient Dashboard - ' . APP_NAME;
require_once '../middleware/auth.php';
require_once '../middleware/role_check.php';
requireRole('patient');

require_once '../classes/Appointment.php';
require_once '../classes/Payment.php';
require_once '../classes/Notification.php';

$user = getCurrentUser();
$appointment = new Appointment();
$payment = new Payment();
$notification = new Notification();

// Get dashboard data
$upcomingAppointments = $appointment->getPatientAppointments($user['user_id'], 5);
$recentPayments = $payment->getPatientPayments($user['user_id'], 5);
$recentNotifications = $notification->getPatientNotifications($user['user_id'], 5);

// Get stats
$totalAppointments = count($appointment->getPatientAppointments($user['user_id'], 100));
$totalPaid = array_sum(array_column($payment->getPatientPayments($user['user_id'], 100), 'amount'));
$pendingPayments = array_sum(array_filter($payment->getPatientPayments($user['user_id'], 100), function($payment) {
    return $payment['status_descr'] === 'Pending';
}));

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Welcome back, <?php echo htmlspecialchars($user['user_name']); ?>!</h1>
                    <p class="text-muted mb-0">Manage your healthcare appointments and payments</p>
                </div>
                <div>
                    <a href="book_appointment.php" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-2"></i>Book Appointment
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
                                <i class="fas fa-calendar-check text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Total Appointments</h6>
                            <h3 class="card-text mb-0"><?php echo $totalAppointments; ?></h3>
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
                                <i class="fas fa-credit-card text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Total Paid</h6>
                            <h3 class="card-text mb-0">$<?php echo number_format($totalPaid, 2); ?></h3>
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
                            <h6 class="card-title text-muted mb-0">Pending Payments</h6>
                            <h3 class="card-text mb-0">$<?php echo number_format($pendingPayments, 2); ?></h3>
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
                                <i class="fas fa-bell text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-0">Notifications</h6>
                            <h3 class="card-text mb-0"><?php echo count($recentNotifications); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Upcoming Appointments -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>Upcoming Appointments
                    </h5>
                    <a href="my_appointments.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($upcomingAppointments)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($upcomingAppointments as $apt): ?>
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Dr. <?php echo htmlspecialchars($apt['Prov_Name']); ?></h6>
                                            <p class="mb-1 text-muted"><?php echo htmlspecialchars($apt['Prov_Spec']); ?></p>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('M j, Y \a\t g:i A', strtotime($apt['DateTime'])); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-<?php echo $apt['Status_Descr'] === 'Scheduled' ? 'primary' : 'success'; ?> rounded-pill">
                                            <?php echo htmlspecialchars($apt['Status_Descr']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No upcoming appointments</h6>
                            <p class="text-muted">Book your first appointment to get started</p>
                            <a href="book_appointment.php" class="btn btn-primary">Book Appointment</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Notifications -->
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
                        <a href="book_appointment.php" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-plus me-2"></i>Book New Appointment
                        </a>
                        <a href="my_appointments.php" class="btn btn-outline-info">
                            <i class="fas fa-calendar-check me-2"></i>View My Appointments
                        </a>
                        <a href="payments.php" class="btn btn-outline-success">
                            <i class="fas fa-credit-card me-2"></i>Manage Payments
                        </a>
                        <a href="profile.php" class="btn btn-outline-secondary">
                            <i class="fas fa-user-edit me-2"></i>Update Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Notifications -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bell me-2 text-info"></i>Recent Notifications
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentNotifications)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($recentNotifications, 0, 3) as $notif): ?>
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-circle text-primary" style="font-size: 0.5rem;"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <p class="mb-1 small"><?php echo htmlspecialchars($notif['Message']); ?></p>
                                            <small class="text-muted">
                                                <?php echo date('M j, g:i A', strtotime($notif['SentDate'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No recent notifications</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

