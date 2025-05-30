<?php
require_once 'Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login($email, $password, $role) {
        try {
            // Determine table based on role
            $table = $this->getRoleTable($role);
            $idField = $this->getRoleIdField($role);
            $nameField = $this->getRoleNameField($role);
            $emailField = $this->getRoleEmailField($role);
            
            if (!$table) {
                return ['success' => false, 'message' => 'Invalid role specified.'];
            }
            
            // For now, we'll use a simple password check since the database doesn't have password fields
            // In a real application, you would have password hashes stored in the database
            $sql = "SELECT {$idField}, {$nameField}, {$emailField} FROM {$table} WHERE {$emailField} = ?";
            $user = $this->db->fetch($sql, [$email]);
            
            if ($user) {
                // For demo purposes, accept any password for existing users
                // In production, you would verify against hashed passwords
                return [
                    'success' => true,
                    'user_id' => $user[$idField],
                    'user_name' => $user[$nameField],
                    'user_email' => $user[$emailField],
                    'user_role' => $role,
                    'message' => 'Login successful.'
                ];
            } else {
                return ['success' => false, 'message' => 'Invalid email or password.'];
            }
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }
    
    public function register($data) {
        try {
            $role = $data['role'];
            $table = $this->getRoleTable($role);
            
            if (!$table) {
                return ['success' => false, 'message' => 'Invalid role specified.'];
            }
            
            // Check if email already exists
            $emailField = $this->getRoleEmailField($role);
            $existingUser = $this->db->fetch("SELECT {$emailField} FROM {$table} WHERE {$emailField} = ?", [$data['email']]);
            
            if ($existingUser) {
                return ['success' => false, 'message' => 'Email address already exists.'];
            }
            
            // Insert new user
            if ($role === 'patient') {
                $sql = "INSERT INTO patient (Pat_Name, Pat_Email, Pat_Phone, Pat_Addr, Pat_DOB) VALUES (?, ?, ?, ?, ?)";
                $params = [$data['name'], $data['email'], $data['phone'], $data['address'], $data['dob']];
            } elseif ($role === 'provider') {
                $sql = "INSERT INTO healthcareprovider (Prov_Name, Prov_Email, Prov_Phone, Prov_Spec) VALUES (?, ?, ?, ?)";
                $params = [$data['name'], $data['email'], $data['phone'], 'General Practice'];
            } else {
                return ['success' => false, 'message' => 'Registration not available for this role.'];
            }
            
            $this->db->execute($sql, $params);
            
            return ['success' => true, 'message' => 'Registration successful.'];
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }
    
    public function getUserById($id, $role) {
        try {
            $table = $this->getRoleTable($role);
            $idField = $this->getRoleIdField($role);
            
            if (!$table) {
                return null;
            }
            
            $sql = "SELECT * FROM {$table} WHERE {$idField} = ?";
            return $this->db->fetch($sql, [$id]);
            
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
    
    public function updateProfile($id, $role, $data) {
        try {
            $table = $this->getRoleTable($role);
            $idField = $this->getRoleIdField($role);
            
            if (!$table) {
                return ['success' => false, 'message' => 'Invalid role specified.'];
            }
            
            if ($role === 'patient') {
                $sql = "UPDATE patient SET Pat_Name = ?, Pat_Phone = ?, Pat_Addr = ? WHERE Pat_ID = ?";
                $params = [$data['name'], $data['phone'], $data['address'], $id];
            } elseif ($role === 'provider') {
                $sql = "UPDATE healthcareprovider SET Prov_Name = ?, Prov_Phone = ?, Prov_Spec = ? WHERE Prov_ID = ?";
                $params = [$data['name'], $data['phone'], $data['specialty'], $id];
            } else {
                return ['success' => false, 'message' => 'Profile update not available for this role.'];
            }
            
            $this->db->execute($sql, $params);
            
            return ['success' => true, 'message' => 'Profile updated successfully.'];
            
        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Profile update failed. Please try again.'];
        }
    }
    
    private function getRoleTable($role) {
        switch ($role) {
            case 'patient':
                return 'patient';
            case 'provider':
                return 'healthcareprovider';
            case 'admin':
                return 'admin_users'; // This table would need to be created
            default:
                return null;
        }
    }
    
    private function getRoleIdField($role) {
        switch ($role) {
            case 'patient':
                return 'Pat_ID';
            case 'provider':
                return 'Prov_ID';
            case 'admin':
                return 'Admin_ID';
            default:
                return null;
        }
    }
    
    private function getRoleNameField($role) {
        switch ($role) {
            case 'patient':
                return 'Pat_Name';
            case 'provider':
                return 'Prov_Name';
            case 'admin':
                return 'Admin_Name';
            default:
                return null;
        }
    }
    
    private function getRoleEmailField($role) {
        switch ($role) {
            case 'patient':
                return 'Pat_Email';
            case 'provider':
                return 'Prov_Email';
            case 'admin':
                return 'Admin_Email';
            default:
                return null;
        }
    }
}
?>
