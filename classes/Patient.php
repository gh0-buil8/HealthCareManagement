<?php
require_once 'Database.php';

class Patient {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAllPatients($limit = 50, $offset = 0, $search = '') {
        try {
            $sql = "SELECT p.*, COUNT(a.Appt_ID) as total_appointments 
                    FROM patients p 
                    LEFT JOIN appointments a ON p.Pat_ID = a.Pat_ID 
                    WHERE p.Pat_Name LIKE ? OR p.Pat_Email LIKE ? 
                    GROUP BY p.Pat_ID 
                    ORDER BY p.Pat_Name ASC 
                    LIMIT ? OFFSET ?";
            
            $searchTerm = "%{$search}%";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm, $limit, $offset]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get all patients error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPatientById($id) {
        try {
            $sql = "SELECT * FROM patients WHERE Pat_ID = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Get patient by ID error: " . $e->getMessage());
            return null;
        }
    }
    
    public function getPatientDetailsWithAppointments($id) {
        try {
            // Get patient details
            $patientSql = "SELECT * FROM patients WHERE Pat_ID = ?";
            $stmt = $this->db->getConnection()->prepare($patientSql);
            $stmt->execute([$id]);
            $patient = $stmt->fetch();
            
            if (!$patient) {
                return null;
            }
            
            // Get patient's appointments with provider details
            $appointmentsSql = "SELECT a.*, p.Prov_Name, p.Prov_Spec, s.Status_Descr, s.color_code
                               FROM appointments a
                               JOIN providers p ON a.Prov_ID = p.Prov_ID
                               JOIN appointment_status s ON a.Status_ID = s.Status_ID
                               WHERE a.Pat_ID = ?
                               ORDER BY a.DateTime DESC";
            $stmt = $this->db->getConnection()->prepare($appointmentsSql);
            $stmt->execute([$id]);
            $appointments = $stmt->fetchAll();
            
            // Get payment history
            $paymentsSql = "SELECT pay.*, pm.MethodName, ps.Status_Descr as payment_status
                           FROM payments pay
                           LEFT JOIN payment_methods pm ON pay.PaymentMeth_ID = pm.PaymentMeth_ID
                           LEFT JOIN payment_status ps ON pay.PaymentStat_ID = ps.PaymentStat_ID
                           WHERE pay.Pat_ID = ?
                           ORDER BY pay.payment_date DESC";
            $stmt = $this->db->getConnection()->prepare($paymentsSql);
            $stmt->execute([$id]);
            $payments = $stmt->fetchAll();
            
            return [
                'patient' => $patient,
                'appointments' => $appointments,
                'payments' => $payments
            ];
        } catch (Exception $e) {
            error_log("Get patient details with appointments error: " . $e->getMessage());
            return null;
        }
    }
    
    public function createPatient($data) {
        try {
            $sql = "INSERT INTO patient (Pat_Name, Pat_Email, Pat_Phone, Pat_Addr, Pat_DOB) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $this->db->execute($sql, [
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['address'],
                $data['dob']
            ]);
            
            return [
                'success' => true,
                'message' => 'Patient created successfully.',
                'patient_id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            error_log("Create patient error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create patient.'];
        }
    }
    
    public function updatePatient($id, $data) {
        try {
            $sql = "UPDATE patient 
                    SET Pat_Name = ?, Pat_Email = ?, Pat_Phone = ?, Pat_Addr = ?, Pat_DOB = ? 
                    WHERE Pat_ID = ?";
            
            $this->db->execute($sql, [
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['address'],
                $data['dob'],
                $id
            ]);
            
            return ['success' => true, 'message' => 'Patient updated successfully.'];
        } catch (Exception $e) {
            error_log("Update patient error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update patient.'];
        }
    }
    
    public function deletePatient($id) {
        try {
            // Check if patient has appointments
            $appointmentCheck = $this->db->fetch("SELECT COUNT(*) as count FROM appointment WHERE Pat_ID = ?", [$id]);
            
            if ($appointmentCheck['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete patient with existing appointments.'];
            }
            
            $sql = "DELETE FROM patient WHERE Pat_ID = ?";
            $this->db->execute($sql, [$id]);
            
            return ['success' => true, 'message' => 'Patient deleted successfully.'];
        } catch (Exception $e) {
            error_log("Delete patient error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete patient.'];
        }
    }
    
    public function getPatientAppointments($patientId, $limit = 10) {
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
    
    public function getPatientPayments($patientId, $limit = 10) {
        try {
            $sql = "SELECT p.*, pm.MethodName, ps.Status_Descr 
                    FROM payment p 
                    JOIN paymentmethod pm ON p.PaymentMeth_ID = pm.PaymentMeth_ID 
                    JOIN paymentstatus ps ON p.PaymentStat_ID = ps.PaymentStat_ID 
                    WHERE p.Pat_ID = ? 
                    ORDER BY p.Payment_ID DESC 
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$patientId, $limit]);
        } catch (Exception $e) {
            error_log("Get patient payments error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPatientStats($patientId) {
        try {
            $stats = [
                'total_appointments' => 0,
                'completed_appointments' => 0,
                'cancelled_appointments' => 0,
                'no_show_appointments' => 0,
                'total_payments' => 0,
                'pending_payments' => 0
            ];
            
            // Appointment stats
            $appointmentStats = $this->db->fetchAll(
                "SELECT ast.Status_Descr, COUNT(*) as count 
                 FROM appointment a 
                 JOIN appointmentstatus ast ON a.Status_ID = ast.Status_ID 
                 WHERE a.Pat_ID = ? 
                 GROUP BY a.Status_ID", 
                [$patientId]
            );
            
            foreach ($appointmentStats as $stat) {
                $stats['total_appointments'] += $stat['count'];
                switch ($stat['Status_Descr']) {
                    case 'Completed':
                        $stats['completed_appointments'] = $stat['count'];
                        break;
                    case 'Cancelled':
                        $stats['cancelled_appointments'] = $stat['count'];
                        break;
                    case 'No Show':
                        $stats['no_show_appointments'] = $stat['count'];
                        break;
                }
            }
            
            // Payment stats
            $paymentStats = $this->db->fetch(
                "SELECT SUM(Amount) as total_amount, 
                        SUM(CASE WHEN ps.Status_Descr = 'Pending' THEN Amount ELSE 0 END) as pending_amount 
                 FROM payment p 
                 JOIN paymentstatus ps ON p.PaymentStat_ID = ps.PaymentStat_ID 
                 WHERE p.Pat_ID = ?", 
                [$patientId]
            );
            
            $stats['total_payments'] = $paymentStats['total_amount'] ?? 0;
            $stats['pending_payments'] = $paymentStats['pending_amount'] ?? 0;
            
            return $stats;
        } catch (Exception $e) {
            error_log("Get patient stats error: " . $e->getMessage());
            return [];
        }
    }
    
    public function searchPatients($query, $limit = 20) {
        try {
            $sql = "SELECT Pat_ID, Pat_Name, Pat_Email, Pat_Phone 
                    FROM patient 
                    WHERE Pat_Name LIKE ? OR Pat_Email LIKE ? OR Pat_Phone LIKE ? 
                    ORDER BY Pat_Name ASC 
                    LIMIT ?";
            
            $searchTerm = "%{$query}%";
            return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
        } catch (Exception $e) {
            error_log("Search patients error: " . $e->getMessage());
            return [];
        }
    }
}
?>
