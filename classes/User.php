<?php
require_once 'Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login($email, $password, $role) {
        try {
            $sql = "SELECT user_id, username, email, password_hash, role, full_name FROM users WHERE email = ? AND role = ?";
            $user = $this->db->getConnection()->prepare($sql);
            $user->execute([$email, $role]);
            $userData = $user->fetch();
            
            if ($userData && password_verify($password, $userData['password_hash'])) {
                return [
                    'success' => true,
                    'user_id' => $userData['user_id'],
                    'user_name' => $userData['full_name'],
                    'user_email' => $userData['email'],
                    'user_role' => $userData['role'],
                    'message' => 'Login successful.'
                ];
            } else {
                return ['success' => false, 'message' => 'Invalid email or password.'];
            }
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database operation failed.'];
        }
    }
    
    public function register($data) {
        try {
            $role = $data['role'];
            
            // Check if email already exists
            $sql = "SELECT 1 FROM users WHERE email = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$data['email']]);
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                return ['success' => false, 'message' => 'Email address already exists.'];
            }
            
            // Hash password
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert into users table
            $sql = "INSERT INTO users (username, email, password_hash, role, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                strtolower(str_replace(' ', '', $data['name'])),
                $data['email'],
                $passwordHash,
                $role,
                $data['name'],
                $data['phone'] ?? ''
            ]);
            
            $userId = $this->db->getConnection()->lastInsertId();
            
            // Insert into role-specific table
            if ($role === 'patient') {
                $sql = "INSERT INTO patients (user_id, Pat_Name, Pat_Email, Pat_Phone, Pat_Address, Pat_DOB) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([
                    $userId,
                    $data['name'],
                    $data['email'],
                    $data['phone'] ?? '',
                    $data['address'] ?? '',
                    $data['dob'] ?? null
                ]);
            } elseif ($role === 'provider') {
                $sql = "INSERT INTO providers (user_id, Prov_Name, Prov_Email, Prov_Phone, Prov_Spec) VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([
                    $userId,
                    $data['name'],
                    $data['email'],
                    $data['phone'] ?? '',
                    $data['specialty'] ?? 'General Practice'
                ]);
            } elseif ($role === 'admin') {
                // For admin users, we don't need a separate table entry
                // They can manage the system using the main users table data
            }
            
            return ['success' => true, 'message' => 'Registration successful.'];
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }
    
    public function getUserById($id, $role) {
        try {
            $sql = "SELECT u.*, 
                    CASE 
                        WHEN u.role = 'patient' THEN p.Pat_Name
                        WHEN u.role = 'provider' THEN pr.Prov_Name
                        ELSE u.full_name
                    END as display_name
                    FROM users u
                    LEFT JOIN patients p ON u.user_id = p.user_id AND u.role = 'patient'
                    LEFT JOIN providers pr ON u.user_id = pr.user_id AND u.role = 'provider'
                    WHERE u.user_id = ? AND u.role = ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$id, $role]);
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
    
    public function updateProfile($id, $role, $data) {
        try {
            // Update users table
            $sql = "UPDATE users SET full_name = ?, phone = ? WHERE user_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$data['name'], $data['phone'], $id]);
            
            // Update role-specific table
            if ($role === 'patient') {
                $sql = "UPDATE patients SET Pat_Name = ?, Pat_Phone = ?, Pat_Address = ? WHERE user_id = ?";
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([$data['name'], $data['phone'], $data['address'] ?? '', $id]);
            } elseif ($role === 'provider') {
                $sql = "UPDATE providers SET Prov_Name = ?, Prov_Phone = ?, Prov_Spec = ? WHERE user_id = ?";
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([$data['name'], $data['phone'], $data['specialty'] ?? '', $id]);
            }
            
            return ['success' => true, 'message' => 'Profile updated successfully.'];
            
        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Profile update failed. Please try again.'];
        }
    }
}
?>
