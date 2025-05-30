<?php
require_once 'Database.php';

class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createNotification($data) {
        try {
            $sql = "INSERT INTO notification (Pat_ID, Message, Type_ID, SentDate) VALUES (?, ?, ?, NOW())";
            $this->db->execute($sql, [
                $data['patient_id'],
                $data['message'],
                $data['type_id']
            ]);
            
            return [
                'success' => true,
                'message' => 'Notification created successfully.',
                'notification_id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            error_log("Create notification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create notification.'];
        }
    }
    
    public function getNotificationById($id) {
        try {
            $sql = "SELECT n.*, p.Pat_Name, p.Pat_Email, nt.Type_Descr 
                    FROM notification n 
                    JOIN patient p ON n.Pat_ID = p.Pat_ID 
                    JOIN notificationtype nt ON n.Type_ID = nt.Type_ID 
                    WHERE n.Noti_ID = ?";
            
            return $this->db->fetch($sql, [$id]);
        } catch (Exception $e) {
            error_log("Get notification by ID error: " . $e->getMessage());
            return null;
        }
    }
    
    public function getPatientNotifications($patientId, $limit = 20) {
        try {
            $sql = "SELECT n.*, nt.Type_Descr 
                    FROM notification n 
                    JOIN notificationtype nt ON n.Type_ID = nt.Type_ID 
                    WHERE n.Pat_ID = ? 
                    ORDER BY n.SentDate DESC 
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$patientId, $limit]);
        } catch (Exception $e) {
            error_log("Get patient notifications error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAllNotifications($limit = 50, $offset = 0, $filters = []) {
        try {
            $sql = "SELECT n.*, p.Pat_Name, p.Pat_Email, nt.Type_Descr 
                    FROM notification n 
                    JOIN patient p ON n.Pat_ID = p.Pat_ID 
                    JOIN notificationtype nt ON n.Type_ID = nt.Type_ID 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['patient_id'])) {
                $sql .= " AND n.Pat_ID = ?";
                $params[] = $filters['patient_id'];
            }
            
            if (!empty($filters['type_id'])) {
                $sql .= " AND n.Type_ID = ?";
                $params[] = $filters['type_id'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(n.SentDate) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(n.SentDate) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (n.Message LIKE ? OR p.Pat_Name LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY n.SentDate DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Get all notifications error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getNotificationTypes() {
        try {
            return $this->db->fetchAll("SELECT * FROM notificationtype ORDER BY Type_Descr");
        } catch (Exception $e) {
            error_log("Get notification types error: " . $e->getMessage());
            return [];
        }
    }
    
    public function sendAppointmentReminder($appointmentId) {
        try {
            // Get appointment details
            $appointment = $this->db->fetch(
                "SELECT a.*, p.Pat_Name, p.Pat_Email, hp.Prov_Name 
                 FROM appointment a 
                 JOIN patient p ON a.Pat_ID = p.Pat_ID 
                 JOIN healthcareprovider hp ON a.Prov_ID = hp.Prov_ID 
                 WHERE a.Appt_ID = ?", 
                [$appointmentId]
            );
            
            if (!$appointment) {
                return ['success' => false, 'message' => 'Appointment not found.'];
            }
            
            $message = "Reminder: You have an appointment with Dr. {$appointment['Prov_Name']} on " . 
                      date('M j, Y \a\t g:i A', strtotime($appointment['DateTime']));
            
            $result = $this->createNotification([
                'patient_id' => $appointment['Pat_ID'],
                'message' => $message,
                'type_id' => 1 // Appointment Reminder
            ]);
            
            // Here you would also send email/SMS
            $this->sendEmail($appointment['Pat_Email'], 'Appointment Reminder', $message);
            
            return $result;
        } catch (Exception $e) {
            error_log("Send appointment reminder error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send appointment reminder.'];
        }
    }
    
    public function sendPaymentReminder($paymentId) {
        try {
            $payment = $this->db->fetch(
                "SELECT p.*, pat.Pat_Name, pat.Pat_Email 
                 FROM payment p 
                 JOIN patient pat ON p.Pat_ID = pat.Pat_ID 
                 WHERE p.Payment_ID = ?", 
                [$paymentId]
            );
            
            if (!$payment) {
                return ['success' => false, 'message' => 'Payment not found.'];
            }
            
            $message = "Payment reminder: You have a pending payment of $" . number_format($payment['Amount'], 2);
            
            $result = $this->createNotification([
                'patient_id' => $payment['Pat_ID'],
                'message' => $message,
                'type_id' => 2 // Payment Reminder
            ]);
            
            $this->sendEmail($payment['Pat_Email'], 'Payment Reminder', $message);
            
            return $result;
        } catch (Exception $e) {
            error_log("Send payment reminder error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send payment reminder.'];
        }
    }
    
    public function sendBulkNotification($data) {
        try {
            $this->db->beginTransaction();
            
            $patients = [];
            
            if ($data['send_to'] === 'all') {
                $patients = $this->db->fetchAll("SELECT Pat_ID, Pat_Email FROM patient");
            } elseif ($data['send_to'] === 'specific' && !empty($data['patient_ids'])) {
                $placeholders = str_repeat('?,', count($data['patient_ids']) - 1) . '?';
                $patients = $this->db->fetchAll(
                    "SELECT Pat_ID, Pat_Email FROM patient WHERE Pat_ID IN ($placeholders)", 
                    $data['patient_ids']
                );
            }
            
            $successCount = 0;
            
            foreach ($patients as $patient) {
                $result = $this->createNotification([
                    'patient_id' => $patient['Pat_ID'],
                    'message' => $data['message'],
                    'type_id' => $data['type_id']
                ]);
                
                if ($result['success']) {
                    $successCount++;
                    // Send email if enabled
                    if ($data['send_email']) {
                        $this->sendEmail($patient['Pat_Email'], $data['subject'], $data['message']);
                    }
                }
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => "Notification sent to {$successCount} patients successfully."
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Send bulk notification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send bulk notification.'];
        }
    }
    
    public function markAsRead($notificationId) {
        try {
            // Add a read status column if needed
            // For now, we'll just return success
            return ['success' => true, 'message' => 'Notification marked as read.'];
        } catch (Exception $e) {
            error_log("Mark notification as read error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to mark notification as read.'];
        }
    }
    
    public function deleteNotification($id) {
        try {
            $sql = "DELETE FROM notification WHERE Noti_ID = ?";
            $this->db->execute($sql, [$id]);
            
            return ['success' => true, 'message' => 'Notification deleted successfully.'];
        } catch (Exception $e) {
            error_log("Delete notification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete notification.'];
        }
    }
    
    private function sendEmail($to, $subject, $message) {
        try {
            // Basic email functionality - in production, use PHPMailer or similar
            $headers = "From: " . SMTP_FROM . "\r\n";
            $headers .= "Reply-To: " . SMTP_FROM . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            $html_message = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #0d6efd;'>" . APP_NAME . "</h2>
                    <p>{$message}</p>
                    <hr style='margin: 20px 0;'>
                    <p style='font-size: 12px; color: #666;'>
                        This is an automated message. Please do not reply to this email.
                    </p>
                </div>
            </body>
            </html>
            ";
            
            // For development, log email instead of sending
            error_log("Email would be sent to: {$to}, Subject: {$subject}, Message: {$message}");
            
            return true;
        } catch (Exception $e) {
            error_log("Send email error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getNotificationStats($filters = []) {
        try {
            $stats = [
                'total_notifications' => 0,
                'notifications_by_type' => [],
                'recent_notifications' => 0
            ];
            
            // Total notifications
            $total = $this->db->fetch("SELECT COUNT(*) as count FROM notification");
            $stats['total_notifications'] = $total['count'];
            
            // Notifications by type
            $byType = $this->db->fetchAll(
                "SELECT nt.Type_Descr, COUNT(*) as count 
                 FROM notification n 
                 JOIN notificationtype nt ON n.Type_ID = nt.Type_ID 
                 GROUP BY n.Type_ID 
                 ORDER BY count DESC"
            );
            $stats['notifications_by_type'] = $byType;
            
            // Recent notifications (last 7 days)
            $recent = $this->db->fetch(
                "SELECT COUNT(*) as count FROM notification WHERE SentDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            $stats['recent_notifications'] = $recent['count'];
            
            return $stats;
        } catch (Exception $e) {
            error_log("Get notification stats error: " . $e->getMessage());
            return [];
        }
    }
}
?>
