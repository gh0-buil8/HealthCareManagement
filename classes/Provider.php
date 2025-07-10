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
                    LEFT JOIN appointment a ON p.Prov_ID = a.Prov_ID 
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
            $sql = "SELECT * FROM providers WHERE Prov_ID = ?";
            return $this->db->fetch($sql, [$id]);
        } catch (Exception $e) {
            error_log("Get provider by ID error: " . $e->getMessage());
            return null;
        }
    }
    
    public function getProviderByUserId($userId) {
        try {
            $sql = "SELECT * FROM providers WHERE user_id = ?";
            return $this->db->fetch($sql, [$userId]);
        } catch (Exception $e) {
            error_log("Get provider by user ID error: " . $e->getMessage());
            return null;
        }
    }
    
    public function createProvider($data) {
        try {
            $sql = "INSERT INTO providers (user_id, Prov_Name, Prov_Spec, Prov_Email, Prov_Phone) 
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
                'provider_id' => $this->db->getConnection()->lastInsertId()
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
            $appointmentCheck = $this->db->fetch("SELECT COUNT(*) as count FROM appointment WHERE Prov_ID = ?", [$id]);
            
            if ($appointmentCheck['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete provider with existing appointments.'];
            }
            
            $sql = "DELETE FROM providers WHERE Prov_ID = ?";
            $this->db->execute($sql, [$id]);
            
            return ['success' => true, 'message' => 'Provider deleted successfully.'];
        } catch (Exception $e) {
            error_log("Delete provider error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete provider.'];
        }
    }
    
    public function getProviderAppointments($userId, $date = null, $limit = 20) {
        try {
            $sql = "SELECT a.*, p.Pat_Name, p.Pat_Email, p.Pat_Phone, ast.Status_Descr 
                    FROM appointment a 
                    JOIN patient p ON a.Pat_ID = p.Pat_ID 
                    JOIN appointmentstatus ast ON a.Status_ID = ast.Status_ID 
                    JOIN providers pr ON a.Prov_ID = pr.Prov_ID
                    WHERE pr.user_id = ?";
            
            $params = [$userId];
            
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
            $sql = "UPDATE providers SET Prov_Avail = ? WHERE user_id = ?";
            $this->db->execute($sql, [$availability, $userId]);
            
            return ['success' => true, 'message' => 'Availability updated successfully.'];
        } catch (Exception $e) {
            error_log("Update provider availability error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update availability.'];
        }
    }
    
    public function updateAvailability($availabilityData) {
        try {
            // Check if availability record exists
            $existingAvailability = $this->db->fetch(
                "SELECT * FROM provideravailability WHERE Prov_ID = ? AND day_of_week = ?",
                [$availabilityData['provider_id'], $availabilityData['day_of_week']]
            );
            
            if ($existingAvailability) {
                // Update existing record
                $sql = "UPDATE provideravailability 
                        SET start_time = ?, end_time = ?, is_available = ? 
                        WHERE Prov_ID = ? AND day_of_week = ?";
                $this->db->execute($sql, [
                    $availabilityData['start_time'],
                    $availabilityData['end_time'],
                    $availabilityData['is_available'],
                    $availabilityData['provider_id'],
                    $availabilityData['day_of_week']
                ]);
            } else {
                // Insert new record
                $sql = "INSERT INTO provideravailability (Prov_ID, day_of_week, start_time, end_time, is_available) 
                        VALUES (?, ?, ?, ?, ?)";
                $this->db->execute($sql, [
                    $availabilityData['provider_id'],
                    $availabilityData['day_of_week'],
                    $availabilityData['start_time'],
                    $availabilityData['end_time'],
                    $availabilityData['is_available']
                ]);
            }
            
            return ['success' => true, 'message' => 'Availability updated successfully.'];
        } catch (Exception $e) {
            error_log("Update availability error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update availability.'];
        }
    }
    
    public function deleteAvailability($availabilityId) {
        try {
            $sql = "DELETE FROM provideravailability WHERE Avail_ID = ?";
            $this->db->execute($sql, [$availabilityId]);
            
            return ['success' => true, 'message' => 'Availability deleted successfully.'];
        } catch (Exception $e) {
            error_log("Delete availability error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete availability.'];
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
            
            $provider = $this->db->fetch("SELECT Prov_ID FROM providers WHERE user_id = ?", [$userId]);
            if (!$provider) {
                return $stats;
            }
            
            $provId = $provider['Prov_ID'];
            
            $todayCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM appointment WHERE Prov_ID = ? AND DATE(DateTime) = CURRENT_DATE", 
                [$provId]
            );
            $stats['todays_appointments'] = $todayCount['count'] ?? 0;
            
            $completedCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM appointment a 
                 JOIN appointmentstatus ast ON a.Status_ID = ast.Status_ID 
                 WHERE a.Prov_ID = ? AND ast.Status_Descr = 'Completed'", 
                [$provId]
            );
            $stats['completed_appointments'] = $completedCount['count'] ?? 0;
            
            $pendingCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM appointment a 
                 JOIN appointmentstatus ast ON a.Status_ID = ast.Status_ID 
                 WHERE a.Prov_ID = ? AND ast.Status_Descr IN ('Scheduled', 'Confirmed')", 
                [$provId]
            );
            $stats['pending_appointments'] = $pendingCount['count'] ?? 0;
            
            $totalCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM appointment WHERE Prov_ID = ?", 
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
                $sql .= " AND p.Prov_Spec = ?";
                $params[] = $specialty;
            }
            
            $sql .= " ORDER BY p.Prov_Name ASC";
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Get available providers error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getSpecialties() {
        try {
            $sql = "SELECT DISTINCT Prov_Spec FROM providers WHERE Prov_Spec IS NOT NULL ORDER BY Prov_Spec ASC";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            error_log("Get specialties error: " . $e->getMessage());
            return [];
        }
    }
}
?>