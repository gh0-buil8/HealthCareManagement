<?php
require_once 'Database.php';

class Appointment {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createAppointment($data) {
        try {
            $this->db->beginTransaction();
            
            // Check if the time slot is available
            if (!$this->isTimeSlotAvailable($data['provider_id'], $data['datetime'])) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Time slot is not available.'];
            }
            
            $sql = "INSERT INTO appointment (Pat_ID, Prov_ID, DateTime, Status_ID) VALUES (?, ?, ?, ?)";
            $this->db->execute($sql, [
                $data['patient_id'],
                $data['provider_id'],
                $data['datetime'],
                1 // Scheduled status
            ]);
            
            $appointmentId = $this->db->lastInsertId();
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Appointment booked successfully.',
                'appointment_id' => $appointmentId
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Create appointment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to book appointment.'];
        }
    }
    
    public function updateAppointment($id, $data) {
        try {
            $sql = "UPDATE appointment SET DateTime = ?, Status_ID = ? WHERE Appt_ID = ?";
            $this->db->execute($sql, [
                $data['datetime'],
                $data['status_id'],
                $id
            ]);
            
            return ['success' => true, 'message' => 'Appointment updated successfully.'];
        } catch (Exception $e) {
            error_log("Update appointment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update appointment.'];
        }
    }
    
    public function cancelAppointment($id, $userId, $userRole) {
        try {
            // Verify user can cancel this appointment
            if (!$this->canUserModifyAppointment($id, $userId, $userRole)) {
                return ['success' => false, 'message' => 'You do not have permission to cancel this appointment.'];
            }
            
            $sql = "UPDATE appointment SET Status_ID = ? WHERE Appt_ID = ?";
            $this->db->execute($sql, [3, $id]); // 3 = Cancelled status
            
            return ['success' => true, 'message' => 'Appointment cancelled successfully.'];
        } catch (Exception $e) {
            error_log("Cancel appointment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to cancel appointment.'];
        }
    }
    
    public function rescheduleAppointment($id, $newDateTime, $userId, $userRole) {
        try {
            // Verify user can reschedule this appointment
            if (!$this->canUserModifyAppointment($id, $userId, $userRole)) {
                return ['success' => false, 'message' => 'You do not have permission to reschedule this appointment.'];
            }
            
            // Get appointment details
            $appointment = $this->getAppointmentById($id);
            if (!$appointment) {
                return ['success' => false, 'message' => 'Appointment not found.'];
            }
            
            // Check if new time slot is available
            if (!$this->isTimeSlotAvailable($appointment['Prov_ID'], $newDateTime, $id)) {
                return ['success' => false, 'message' => 'New time slot is not available.'];
            }
            
            $sql = "UPDATE appointment SET DateTime = ?, Status_ID = ? WHERE Appt_ID = ?";
            $this->db->execute($sql, [$newDateTime, 4, $id]); // 4 = Rescheduled status
            
            return ['success' => true, 'message' => 'Appointment rescheduled successfully.'];
        } catch (Exception $e) {
            error_log("Reschedule appointment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to reschedule appointment.'];
        }
    }
    
    public function getAppointmentById($id) {
        try {
            $sql = "SELECT a.*, p.Pat_Name, p.Pat_Email, p.Pat_Phone, 
                           hp.Prov_Name, hp.Prov_Spec, ast.Status_Descr 
                    FROM appointment a 
                    JOIN patient p ON a.Pat_ID = p.Pat_ID 
                    JOIN healthcareprovider hp ON a.Prov_ID = hp.Prov_ID 
                    JOIN appointmentstatus ast ON a.Status_ID = ast.Status_ID 
                    WHERE a.Appt_ID = ?";
            
            return $this->db->fetch($sql, [$id]);
        } catch (Exception $e) {
            error_log("Get appointment by ID error: " . $e->getMessage());
            return null;
        }
    }
    
    public function getAllAppointments($limit = 50, $offset = 0, $filters = []) {
        try {
            $sql = "SELECT a.*, p.Pat_Name, p.Pat_Email, 
                           hp.Prov_Name, hp.Prov_Spec, ast.Status_Descr 
                    FROM appointment a 
                    JOIN patient p ON a.Pat_ID = p.Pat_ID 
                    JOIN healthcareprovider hp ON a.Prov_ID = hp.Prov_ID 
                    JOIN appointmentstatus ast ON a.Status_ID = ast.Status_ID 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(a.DateTime) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(a.DateTime) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['provider_id'])) {
                $sql .= " AND a.Prov_ID = ?";
                $params[] = $filters['provider_id'];
            }
            
            if (!empty($filters['status_id'])) {
                $sql .= " AND a.Status_ID = ?";
                $params[] = $filters['status_id'];
            }
            
            if (!empty($filters['patient_search'])) {
                $sql .= " AND (p.Pat_Name LIKE ? OR p.Pat_Email LIKE ?)";
                $searchTerm = "%{$filters['patient_search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY a.DateTime DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Get all appointments error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPatientAppointments($patientId, $limit = 20) {
        try {
            $sql = "SELECT a.*, hp.Prov_Name, hp.Prov_Spec, ast.Status_Descr 
                    FROM appointment a 
                    JOIN healthcareprovider hp ON a.Prov_ID = hp.Prov_ID 
                    JOIN appointmentstatus ast ON a.Status_ID = ast.Status_ID 
                    WHERE a.Pat_ID = ? 
                    ORDER BY a.DateTime DESC 
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$patientId, $limit]);
        } catch (Exception $e) {
            error_log("Get patient appointments error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getProviderAppointments($providerId, $date = null, $limit = 20) {
        try {
            $sql = "SELECT a.*, p.Pat_Name, p.Pat_Email, p.Pat_Phone, ast.Status_Descr 
                    FROM appointment a 
                    JOIN patient p ON a.Pat_ID = p.Pat_ID 
                    JOIN appointmentstatus ast ON a.Status_ID = ast.Status_ID 
                    WHERE a.Prov_ID = ?";
            
            $params = [$providerId];
            
            if ($date) {
                $sql .= " AND DATE(a.DateTime) = ?";
                $params[] = $date;
            }
            
            $sql .= " ORDER BY a.DateTime ASC LIMIT ?";
            $params[] = $limit;
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Get provider appointments error: " . $e->getMessage());
            return [];
        }
    }
    
    public function isTimeSlotAvailable($providerId, $datetime, $excludeAppointmentId = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM appointment 
                    WHERE Prov_ID = ? AND DateTime = ? AND Status_ID NOT IN (3, 5)"; // Exclude cancelled and no-show
            
            $params = [$providerId, $datetime];
            
            if ($excludeAppointmentId) {
                $sql .= " AND Appt_ID != ?";
                $params[] = $excludeAppointmentId;
            }
            
            $result = $this->db->fetch($sql, $params);
            return $result['count'] == 0;
        } catch (Exception $e) {
            error_log("Check time slot availability error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAvailableTimeSlots($providerId, $date) {
        try {
            // Generate time slots from 9 AM to 5 PM (8 hours * 4 slots per hour = 32 slots)
            $timeSlots = [];
            $startTime = new DateTime($date . ' 09:00:00');
            $endTime = new DateTime($date . ' 17:00:00');
            
            while ($startTime < $endTime) {
                $timeSlots[] = $startTime->format('H:i');
                $startTime->add(new DateInterval('PT15M')); // 15-minute intervals
            }
            
            // Get booked appointments for this provider on this date
            $bookedSlots = $this->db->fetchAll(
                "SELECT TIME(DateTime) as time_slot FROM appointment 
                 WHERE Prov_ID = ? AND DATE(DateTime) = ? AND Status_ID NOT IN (3, 5)",
                [$providerId, $date]
            );
            
            $bookedTimes = array_column($bookedSlots, 'time_slot');
            
            // Remove booked slots
            $availableSlots = array_filter($timeSlots, function($slot) use ($bookedTimes) {
                return !in_array($slot, $bookedTimes);
            });
            
            return array_values($availableSlots);
        } catch (Exception $e) {
            error_log("Get available time slots error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAppointmentStatuses() {
        try {
            return $this->db->fetchAll("SELECT * FROM appointmentstatus ORDER BY Status_ID");
        } catch (Exception $e) {
            error_log("Get appointment statuses error: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateAppointmentStatus($id, $statusId) {
        try {
            $sql = "UPDATE appointment SET Status_ID = ? WHERE Appt_ID = ?";
            $this->db->execute($sql, [$statusId, $id]);
            
            return ['success' => true, 'message' => 'Appointment status updated successfully.'];
        } catch (Exception $e) {
            error_log("Update appointment status error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update appointment status.'];
        }
    }
    
    private function canUserModifyAppointment($appointmentId, $userId, $userRole) {
        try {
            $appointment = $this->getAppointmentById($appointmentId);
            if (!$appointment) {
                return false;
            }
            
            // Admin can modify any appointment
            if ($userRole === 'admin') {
                return true;
            }
            
            // Provider can modify their own appointments
            if ($userRole === 'provider' && $appointment['Prov_ID'] == $userId) {
                return true;
            }
            
            // Patient can modify their own appointments
            if ($userRole === 'patient' && $appointment['Pat_ID'] == $userId) {
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Check user appointment modification permission error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUpcomingAppointments($limit = 10) {
        try {
            $sql = "SELECT a.*, p.Pat_Name, hp.Prov_Name, ast.Status_Descr 
                    FROM appointment a 
                    JOIN patient p ON a.Pat_ID = p.Pat_ID 
                    JOIN healthcareprovider hp ON a.Prov_ID = hp.Prov_ID 
                    JOIN appointmentstatus ast ON a.Status_ID = ast.Status_ID 
                    WHERE a.DateTime > NOW() AND a.Status_ID IN (1, 9) 
                    ORDER BY a.DateTime ASC 
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$limit]);
        } catch (Exception $e) {
            error_log("Get upcoming appointments error: " . $e->getMessage());
            return [];
        }
    }
}
?>
