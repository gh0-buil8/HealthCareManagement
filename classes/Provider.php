<?php
require_once 'Database.php';

class Provider {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAllProviders($limit = 50, $offset = 0, $search = '') {
        try {
            $sql = "SELECT p.*, COUNT(a.Appt_ID) as total_appointments 
                    FROM providers p 
                    LEFT JOIN appointments a ON p.Prov_ID = a.Prov_ID 
                    WHERE p.Prov_Name LIKE ? OR p.Prov_Spec LIKE ? 
                    GROUP BY p.Prov_ID 
                    ORDER BY p.Prov_Name ASC 
                    LIMIT ? OFFSET ?";
            
            $searchTerm = "%{$search}%";
            return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $limit, $offset]);
        } catch (Exception $e) {
            error_log("Get all providers error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getProviderById($id) {
        try {
            $sql = "SELECT * FROM providers WHERE user_id = ?";
            return $this->db->fetch($sql, [$id]);
        } catch (Exception $e) {
            error_log("Get provider by ID error: " . $e->getMessage());
            return null;
        }
    }
    
    public function createProvider($data) {
        try {
            $sql = "INSERT INTO providers (user_id, prov_name, prov_spec, prov_email, prov_phone) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $this->db->execute($sql, [
                $data['user_id'],
                $data['name'],
                $data['specialization'],
                $data['email'],
                $data['phone']
            ]);
            
            return [
                'success' => true,
                'message' => 'Provider created successfully.',
                'provider_id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            error_log("Create provider error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create provider.'];
        }
    }
    
    public function updateProvider($id, $data) {
        try {
            $sql = "UPDATE providers 
                    SET Prov_Name = ?, Prov_Spec = ?, Prov_Email = ?, Prov_Phone = ?, 
                        license_number = ?, education = ?, experience_years = ?, consultation_fee = ?
                    WHERE user_id = ?";
            
            $this->db->execute($sql, [
                $data['name'] ?? '',
                $data['specialization'] ?? '',
                $data['email'] ?? '',
                $data['phone'] ?? '',
                $data['license_number'] ?? '',
                $data['education'] ?? '',
                $data['experience_years'] ?? 0,
                $data['consultation_fee'] ?? 100.00,
                $id
            ]);
            
            return ['success' => true, 'message' => 'Provider profile updated successfully.'];
        } catch (Exception $e) {
            error_log("Update provider error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update provider profile.'];
        }
    }
    
    public function deleteProvider($id) {
        try {
            $appointmentCheck = $this->db->fetch("SELECT COUNT(*) as count FROM appointments WHERE prov_id = ?", [$id]);
            
            if ($appointmentCheck['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete provider with existing appointments.'];
            }
            
            $sql = "DELETE FROM providers WHERE prov_id = ?";
            $this->db->execute($sql, [$id]);
            
            return ['success' => true, 'message' => 'Provider deleted successfully.'];
        } catch (Exception $e) {
            error_log("Delete provider error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete provider.'];
        }
    }
    
    public function getProviderAppointments($userId, $date = null, $limit = 20) {
        try {
            $sql = "SELECT a.*, p.pat_name, p.pat_email, p.pat_phone, ast.status_descr 
                    FROM appointments a 
                    JOIN patients p ON a.pat_id = p.pat_id 
                    JOIN appointment_status ast ON a.status_id = ast.status_id 
                    JOIN providers pr ON a.prov_id = pr.prov_id
                    WHERE pr.user_id = ?";
            
            $params = [$userId];
            
            if ($date) {
                $sql .= " AND DATE(a.datetime) = ?";
                $params[] = $date;
            }
            
            $sql .= " ORDER BY a.datetime ASC LIMIT ?";
            $params[] = $limit;
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Get provider appointments error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getProviderAvailability($userId) {
        try {
            $sql = "SELECT p.* FROM providers p WHERE p.user_id = ?";
            return $this->db->fetch($sql, [$userId]);
        } catch (Exception $e) {
            error_log("Get provider availability error: " . $e->getMessage());
            return null;
        }
    }
    
    public function updateProviderAvailability($userId, $availability) {
        try {
            $sql = "UPDATE providers SET available_days = ? WHERE user_id = ?";
            $this->db->execute($sql, [$availability, $userId]);
            
            return ['success' => true, 'message' => 'Availability updated successfully.'];
        } catch (Exception $e) {
            error_log("Update provider availability error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update availability.'];
        }
    }
    
    public function getProviderStats($userId) {
        try {
            $stats = [
                'todays_appointments' => 0,
                'completed_appointments' => 0,
                'pending_appointments' => 0,
                'total_appointments' => 0
            ];
            
            $provider = $this->db->fetch("SELECT prov_id FROM providers WHERE user_id = ?", [$userId]);
            if (!$provider) {
                return $stats;
            }
            
            $provId = $provider['prov_id'];
            
            $todayCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM appointments WHERE prov_id = ? AND DATE(datetime) = CURRENT_DATE", 
                [$provId]
            );
            $stats['todays_appointments'] = $todayCount['count'] ?? 0;
            
            $completedCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM appointments a 
                 JOIN appointment_status ast ON a.status_id = ast.status_id 
                 WHERE a.prov_id = ? AND ast.status_descr = 'Completed'", 
                [$provId]
            );
            $stats['completed_appointments'] = $completedCount['count'] ?? 0;
            
            $pendingCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM appointments a 
                 JOIN appointment_status ast ON a.status_id = ast.status_id 
                 WHERE a.prov_id = ? AND ast.status_descr IN ('Scheduled', 'Confirmed')", 
                [$provId]
            );
            $stats['pending_appointments'] = $pendingCount['count'] ?? 0;
            
            $totalCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM appointments WHERE prov_id = ?", 
                [$provId]
            );
            $stats['total_appointments'] = $totalCount['count'] ?? 0;
            
            return $stats;
        } catch (Exception $e) {
            error_log("Get provider stats error: " . $e->getMessage());
            return [
                'todays_appointments' => 0,
                'completed_appointments' => 0,
                'pending_appointments' => 0,
                'total_appointments' => 0
            ];
        }
    }
    
    public function getAvailableProviders($specialty = null, $date = null) {
        try {
            $sql = "SELECT p.* FROM providers p WHERE 1=1";
            
            $params = [];
            
            if ($specialty) {
                $sql .= " AND p.prov_spec = ?";
                $params[] = $specialty;
            }
            
            $sql .= " ORDER BY p.prov_name ASC";
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Get available providers error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getSpecialties() {
        try {
            $sql = "SELECT DISTINCT prov_spec FROM providers WHERE prov_spec IS NOT NULL ORDER BY prov_spec ASC";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            error_log("Get specialties error: " . $e->getMessage());
            return [];
        }
    }
}
?>