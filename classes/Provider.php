<?php
require_once 'Database.php';

class Provider {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAllProviders($limit = 50, $offset = 0, $search = '') {
        try {
            $sql = "SELECT hp.*, COUNT(a.Appt_ID) as total_appointments 
                    FROM healthcareprovider hp 
                    LEFT JOIN appointment a ON hp.Prov_ID = a.Prov_ID 
                    WHERE hp.Prov_Name LIKE ? OR hp.Prov_Spec LIKE ? 
                    GROUP BY hp.Prov_ID 
                    ORDER BY hp.Prov_Name ASC 
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
            $sql = "SELECT * FROM healthcareprovider WHERE Prov_ID = ?";
            return $this->db->fetch($sql, [$id]);
        } catch (Exception $e) {
            error_log("Get provider by ID error: " . $e->getMessage());
            return null;
        }
    }
    
    public function createProvider($data) {
        try {
            $sql = "INSERT INTO healthcareprovider (Prov_Name, Prov_Spec, Prov_Email, Prov_Phone) 
                    VALUES (?, ?, ?, ?)";
            
            $this->db->execute($sql, [
                $data['name'],
                $data['specialty'],
                $data['email'],
                $data['phone']
            ]);
            
            return [
                'success' => true,
                'message' => 'Healthcare provider created successfully.',
                'provider_id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            error_log("Create provider error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create healthcare provider.'];
        }
    }
    
    public function updateProvider($id, $data) {
        try {
            $sql = "UPDATE healthcareprovider 
                    SET Prov_Name = ?, Prov_Spec = ?, Prov_Email = ?, Prov_Phone = ? 
                    WHERE Prov_ID = ?";
            
            $this->db->execute($sql, [
                $data['name'],
                $data['specialty'],
                $data['email'],
                $data['phone'],
                $id
            ]);
            
            return ['success' => true, 'message' => 'Healthcare provider updated successfully.'];
        } catch (Exception $e) {
            error_log("Update provider error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update healthcare provider.'];
        }
    }
    
    public function deleteProvider($id) {
        try {
            // Check if provider has appointments
            $appointmentCheck = $this->db->fetch("SELECT COUNT(*) as count FROM appointment WHERE Prov_ID = ?", [$id]);
            
            if ($appointmentCheck['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete provider with existing appointments.'];
            }
            
            $sql = "DELETE FROM healthcareprovider WHERE Prov_ID = ?";
            $this->db->execute($sql, [$id]);
            
            return ['success' => true, 'message' => 'Healthcare provider deleted successfully.'];
        } catch (Exception $e) {
            error_log("Delete provider error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete healthcare provider.'];
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
    
    public function getProviderAvailability($providerId) {
        try {
            $sql = "SELECT * FROM provideravailability WHERE Prov_ID = ?";
            return $this->db->fetch($sql, [$providerId]);
        } catch (Exception $e) {
            error_log("Get provider availability error: " . $e->getMessage());
            return null;
        }
    }
    
    public function updateProviderAvailability($providerId, $availability) {
        try {
            // Check if availability record exists
            $existing = $this->getProviderAvailability($providerId);
            
            if ($existing) {
                $sql = "UPDATE provideravailability SET Prov_Avail = ? WHERE Prov_ID = ?";
                $params = [$availability, $providerId];
            } else {
                $sql = "INSERT INTO provideravailability (Prov_ID, Prov_Avail) VALUES (?, ?)";
                $params = [$providerId, $availability];
            }
            
            $this->db->execute($sql, $params);
            
            return ['success' => true, 'message' => 'Availability updated successfully.'];
        } catch (Exception $e) {
            error_log("Update provider availability error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update availability.'];
        }
    }
    
    public function getProviderStats($providerId) {
        try {
            $stats = [
                'total_appointments' => 0,
                'todays_appointments' => 0,
                'completed_appointments' => 0,
                'cancelled_appointments' => 0,
                'pending_appointments' => 0
            ];
            
            // Total appointments
            $totalAppointments = $this->db->fetch(
                "SELECT COUNT(*) as count FROM appointment WHERE Prov_ID = ?", 
                [$providerId]
            );
            $stats['total_appointments'] = $totalAppointments['count'];
            
            // Today's appointments
            $todaysAppointments = $this->db->fetch(
                "SELECT COUNT(*) as count FROM appointment WHERE Prov_ID = ? AND DATE(DateTime) = CURDATE()", 
                [$providerId]
            );
            $stats['todays_appointments'] = $todaysAppointments['count'];
            
            // Appointment status stats
            $statusStats = $this->db->fetchAll(
                "SELECT ast.Status_Descr, COUNT(*) as count 
                 FROM appointment a 
                 JOIN appointmentstatus ast ON a.Status_ID = ast.Status_ID 
                 WHERE a.Prov_ID = ? 
                 GROUP BY a.Status_ID", 
                [$providerId]
            );
            
            foreach ($statusStats as $stat) {
                switch ($stat['Status_Descr']) {
                    case 'Completed':
                        $stats['completed_appointments'] = $stat['count'];
                        break;
                    case 'Cancelled':
                        $stats['cancelled_appointments'] = $stat['count'];
                        break;
                    case 'Scheduled':
                    case 'Confirmed':
                        $stats['pending_appointments'] += $stat['count'];
                        break;
                }
            }
            
            return $stats;
        } catch (Exception $e) {
            error_log("Get provider stats error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAvailableProviders($specialty = null, $date = null) {
        try {
            $sql = "SELECT hp.*, pa.Prov_Avail 
                    FROM healthcareprovider hp 
                    LEFT JOIN provideravailability pa ON hp.Prov_ID = pa.Prov_ID 
                    WHERE 1=1";
            
            $params = [];
            
            if ($specialty) {
                $sql .= " AND hp.Prov_Spec = ?";
                $params[] = $specialty;
            }
            
            $sql .= " ORDER BY hp.Prov_Name ASC";
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Get available providers error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getSpecialties() {
        try {
            $sql = "SELECT DISTINCT Prov_Spec FROM healthcareprovider WHERE Prov_Spec IS NOT NULL ORDER BY Prov_Spec ASC";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            error_log("Get specialties error: " . $e->getMessage());
            return [];
        }
    }
}
?>
