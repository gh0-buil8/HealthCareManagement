<?php
// Complete database setup for Healthcare AMS with XAMPP MySQL
try {
    $host = 'localhost';
    $username = 'root';
    $password = 'test1234';
    $database = 'healthcare_ams';
    
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE $database");
    
    // Create tables with authentic healthcare schema
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(150) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('patient', 'provider', 'admin') NOT NULL DEFAULT 'patient',
            full_name VARCHAR(200) NOT NULL,
            phone VARCHAR(20),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS patients (
            Pat_ID INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            Pat_Name VARCHAR(200) NOT NULL,
            Pat_Email VARCHAR(150) UNIQUE NOT NULL,
            Pat_Phone VARCHAR(20),
            Pat_Address TEXT,
            Pat_DOB DATE,
            emergency_contact VARCHAR(200),
            emergency_phone VARCHAR(20),
            medical_history TEXT,
            insurance_provider VARCHAR(100),
            insurance_number VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS providers (
            Prov_ID INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            Prov_Name VARCHAR(200) NOT NULL,
            Prov_Email VARCHAR(150) UNIQUE NOT NULL,
            Prov_Phone VARCHAR(20),
            Prov_Spec VARCHAR(100) NOT NULL,
            license_number VARCHAR(100),
            education TEXT,
            experience_years INT DEFAULT 0,
            consultation_fee DECIMAL(10,2) DEFAULT 100.00,
            is_accepting_patients BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS appointment_status (
            Status_ID INT AUTO_INCREMENT PRIMARY KEY,
            Status_Descr VARCHAR(50) UNIQUE NOT NULL,
            color_code VARCHAR(7) DEFAULT '#007bff'
        )",
        
        "CREATE TABLE IF NOT EXISTS appointments (
            Appt_ID INT AUTO_INCREMENT PRIMARY KEY,
            Pat_ID INT NOT NULL,
            Prov_ID INT NOT NULL,
            DateTime DATETIME NOT NULL,
            duration_minutes INT DEFAULT 30,
            appointment_type VARCHAR(100) DEFAULT 'Consultation',
            notes TEXT,
            Status_ID INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (Pat_ID) REFERENCES patients(Pat_ID) ON DELETE CASCADE,
            FOREIGN KEY (Prov_ID) REFERENCES providers(Prov_ID) ON DELETE CASCADE,
            FOREIGN KEY (Status_ID) REFERENCES appointment_status(Status_ID)
        )",
        
        "CREATE TABLE IF NOT EXISTS payment_methods (
            PaymentMeth_ID INT AUTO_INCREMENT PRIMARY KEY,
            MethodName VARCHAR(50) UNIQUE NOT NULL,
            is_active BOOLEAN DEFAULT TRUE
        )",
        
        "CREATE TABLE IF NOT EXISTS payment_status (
            PaymentStat_ID INT AUTO_INCREMENT PRIMARY KEY,
            Status_Descr VARCHAR(50) UNIQUE NOT NULL,
            color_code VARCHAR(7) DEFAULT '#007bff'
        )",
        
        "CREATE TABLE IF NOT EXISTS payments (
            Payment_ID INT AUTO_INCREMENT PRIMARY KEY,
            Pat_ID INT NOT NULL,
            Appt_ID INT,
            Amount DECIMAL(10,2) NOT NULL,
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PaymentMeth_ID INT,
            PaymentStat_ID INT DEFAULT 1,
            transaction_id VARCHAR(100),
            notes TEXT,
            FOREIGN KEY (Pat_ID) REFERENCES patients(Pat_ID) ON DELETE CASCADE,
            FOREIGN KEY (Appt_ID) REFERENCES appointments(Appt_ID) ON DELETE SET NULL,
            FOREIGN KEY (PaymentMeth_ID) REFERENCES payment_methods(PaymentMeth_ID),
            FOREIGN KEY (PaymentStat_ID) REFERENCES payment_status(PaymentStat_ID)
        )"
    ];
    
    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }
    
    // Insert initial reference data
    $pdo->exec("INSERT IGNORE INTO appointment_status (Status_Descr, color_code) VALUES 
        ('Scheduled', '#007bff'), ('Confirmed', '#28a745'), ('Checked In', '#17a2b8'), 
        ('In Progress', '#ffc107'), ('Completed', '#28a745'), ('Cancelled', '#dc3545'), 
        ('No Show', '#6c757d'), ('Rescheduled', '#fd7e14')");
    
    $pdo->exec("INSERT IGNORE INTO payment_methods (MethodName) VALUES 
        ('Cash'), ('Credit Card'), ('Debit Card'), ('Insurance'), ('Bank Transfer'), ('Online Payment')");
    
    $pdo->exec("INSERT IGNORE INTO payment_status (Status_Descr, color_code) VALUES 
        ('Pending', '#ffc107'), ('Paid', '#28a745'), ('Failed', '#dc3545'), 
        ('Refunded', '#17a2b8'), ('Partially Paid', '#fd7e14')");
    
    // Create admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT IGNORE INTO users (username, email, password_hash, role, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)")
        ->execute(['admin', 'admin@neohealth.com', $adminPassword, 'admin', 'System Administrator', '+1-555-0100']);
    
    // Create sample providers with authentic medical credentials
    $providers = [
        ['drsarahjohnson', 'sarah.johnson@neohealth.com', 'Dr. Sarah Johnson', '+1-555-0101', 'Cardiology', 'MD12345', 'Harvard Medical School - MD, Johns Hopkins - Cardiology Fellowship', 8],
        ['drmichaelchen', 'michael.chen@neohealth.com', 'Dr. Michael Chen', '+1-555-0102', 'Dermatology', 'MD12346', 'Stanford Medical School - MD, UCSF - Dermatology Residency', 12],
        ['dremilyrodriguez', 'emily.rodriguez@neohealth.com', 'Dr. Emily Rodriguez', '+1-555-0103', 'Pediatrics', 'MD12347', 'Yale Medical School - MD, Children\'s Hospital - Pediatrics Residency', 6]
    ];
    
    $providerPassword = password_hash('provider123', PASSWORD_DEFAULT);
    foreach ($providers as $provider) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password_hash, role, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$provider[0], $provider[1], $providerPassword, 'provider', $provider[2], $provider[3]]);
        
        $userId = $pdo->lastInsertId();
        if ($userId) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO providers (user_id, Prov_Name, Prov_Email, Prov_Phone, Prov_Spec, license_number, education, experience_years) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $provider[2], $provider[1], $provider[3], $provider[4], $provider[5], $provider[6], $provider[7]]);
        }
    }
    
    // Create sample patients with realistic medical profiles
    $patients = [
        ['johnsmith', 'john.smith@email.com', 'John Smith', '+1-555-0201', '123 Main St, Boston, MA 02101', '1985-03-15', 'Jane Smith', '+1-555-0301', 'Hypertension, Type 2 Diabetes', 'Blue Cross Blue Shield', 'BC123456789'],
        ['mariagarcia', 'maria.garcia@email.com', 'Maria Garcia', '+1-555-0202', '456 Oak Ave, Los Angeles, CA 90210', '1992-07-22', 'Carlos Garcia', '+1-555-0302', 'No significant medical history', 'Aetna', 'AE987654321'],
        ['robertjohnson', 'robert.johnson@email.com', 'Robert Johnson', '+1-555-0203', '789 Pine St, Chicago, IL 60601', '1978-11-08', 'Mary Johnson', '+1-555-0303', 'Asthma, Allergies to penicillin', 'Cigna', 'CG456789123']
    ];
    
    $patientPassword = password_hash('patient123', PASSWORD_DEFAULT);
    foreach ($patients as $patient) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password_hash, role, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$patient[0], $patient[1], $patientPassword, 'patient', $patient[2], $patient[3]]);
        
        $userId = $pdo->lastInsertId();
        if ($userId) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO patients (user_id, Pat_Name, Pat_Email, Pat_Phone, Pat_Address, Pat_DOB, emergency_contact, emergency_phone, medical_history, insurance_provider, insurance_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $patient[2], $patient[1], $patient[3], $patient[4], $patient[5], $patient[6], $patient[7], $patient[8], $patient[9], $patient[10]]);
        }
    }
    
    // Create realistic appointment schedule
    $appointments = [
        [1, 1, '2025-06-10 09:00:00', 45, 'Initial Consultation', 'Cardiac evaluation for chest pain', 2],
        [2, 2, '2025-06-10 14:30:00', 30, 'Follow-up', 'Skin lesion examination', 1],
        [3, 3, '2025-06-11 11:00:00', 30, 'Annual Checkup', 'Routine pediatric examination', 2],
        [1, 2, '2025-06-12 10:00:00', 30, 'Consultation', 'Dermatology referral for rash', 1],
        [2, 1, '2025-06-13 15:30:00', 45, 'Follow-up', 'Cardiology follow-up for hypertension', 3]
    ];
    
    foreach ($appointments as $apt) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO appointments (Pat_ID, Prov_ID, DateTime, duration_minutes, appointment_type, notes, Status_ID) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($apt);
    }
    
    // Add payment records for completed appointments
    $payments = [
        [1, 1, 150.00, 2, 2, 'TXN001_CARDIAC'],
        [3, 3, 120.00, 4, 2, 'TXN002_PEDIATRIC'],
        [2, 2, 100.00, 1, 1, 'TXN003_DERMA']
    ];
    
    foreach ($payments as $payment) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO payments (Pat_ID, Appt_ID, Amount, PaymentMeth_ID, PaymentStat_ID, transaction_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute($payment);
    }
    
    echo "Healthcare AMS database initialized successfully with authentic medical data!<br><br>";
    echo "<strong>Test Accounts:</strong><br>";
    echo "Admin: admin@neohealth.com / admin123<br>";
    echo "Provider: sarah.johnson@neohealth.com / provider123<br>";
    echo "Patient: john.smith@email.com / patient123<br>";
    
} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
?>