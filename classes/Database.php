<?php
class Database {
    private static $instance = null;
    private $connection;
    private $host = 'localhost';
    private $username = 'root';
    private $password = 'test1234';
    private $database = 'healthcare_ams';
    private $charset = "utf8mb4";

    private function __construct() {
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            // Use SQLite for local database operations
            $dbPath = __DIR__ . '/../data/healthcare_ams.db';
            $dataDir = dirname($dbPath);
            
            // Create data directory if it doesn't exist
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0755, true);
            }
            
            $dsn = "sqlite:$dbPath";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            $this->connection = new PDO($dsn, null, null, $options);
            
            // Initialize schema if database is new
            $this->initializeSchema();
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    
    private function initializeSchema() {
        // Check if tables exist
        $result = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        if ($result->fetch()) {
            return; // Schema already exists
        }
        
        // Create tables with authentic healthcare schema
        $this->createTables();
        $this->insertSampleData();
    }
    
    private function createTables() {
        $tables = [
            "CREATE TABLE users (
                user_id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'patient',
                full_name TEXT NOT NULL,
                phone TEXT,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE patients (
                Pat_ID INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                Pat_Name TEXT NOT NULL,
                Pat_Email TEXT UNIQUE NOT NULL,
                Pat_Phone TEXT,
                Pat_Address TEXT,
                Pat_DOB DATE,
                emergency_contact TEXT,
                emergency_phone TEXT,
                medical_history TEXT,
                insurance_provider TEXT,
                insurance_number TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE providers (
                Prov_ID INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                Prov_Name TEXT NOT NULL,
                Prov_Email TEXT UNIQUE NOT NULL,
                Prov_Phone TEXT,
                Prov_Spec TEXT NOT NULL,
                license_number TEXT,
                education TEXT,
                experience_years INTEGER DEFAULT 0,
                consultation_fee DECIMAL(10,2) DEFAULT 100.00,
                is_accepting_patients INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE appointment_status (
                Status_ID INTEGER PRIMARY KEY AUTOINCREMENT,
                Status_Descr TEXT UNIQUE NOT NULL,
                color_code TEXT DEFAULT '#007bff'
            )",
            
            "CREATE TABLE appointments (
                Appt_ID INTEGER PRIMARY KEY AUTOINCREMENT,
                Pat_ID INTEGER NOT NULL,
                Prov_ID INTEGER NOT NULL,
                DateTime DATETIME NOT NULL,
                duration_minutes INTEGER DEFAULT 30,
                appointment_type TEXT DEFAULT 'Consultation',
                notes TEXT,
                Status_ID INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (Pat_ID) REFERENCES patients(Pat_ID) ON DELETE CASCADE,
                FOREIGN KEY (Prov_ID) REFERENCES providers(Prov_ID) ON DELETE CASCADE,
                FOREIGN KEY (Status_ID) REFERENCES appointment_status(Status_ID)
            )",
            
            "CREATE TABLE payment_methods (
                PaymentMeth_ID INTEGER PRIMARY KEY AUTOINCREMENT,
                MethodName TEXT UNIQUE NOT NULL,
                is_active INTEGER DEFAULT 1
            )",
            
            "CREATE TABLE payment_status (
                PaymentStat_ID INTEGER PRIMARY KEY AUTOINCREMENT,
                Status_Descr TEXT UNIQUE NOT NULL,
                color_code TEXT DEFAULT '#007bff'
            )",
            
            "CREATE TABLE payments (
                Payment_ID INTEGER PRIMARY KEY AUTOINCREMENT,
                Pat_ID INTEGER NOT NULL,
                Appt_ID INTEGER,
                Amount DECIMAL(10,2) NOT NULL,
                payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                PaymentMeth_ID INTEGER,
                PaymentStat_ID INTEGER DEFAULT 1,
                transaction_id TEXT,
                notes TEXT,
                FOREIGN KEY (Pat_ID) REFERENCES patients(Pat_ID) ON DELETE CASCADE,
                FOREIGN KEY (Appt_ID) REFERENCES appointments(Appt_ID) ON DELETE SET NULL,
                FOREIGN KEY (PaymentMeth_ID) REFERENCES payment_methods(PaymentMeth_ID),
                FOREIGN KEY (PaymentStat_ID) REFERENCES payment_status(PaymentStat_ID)
            )"
        ];
        
        foreach ($tables as $sql) {
            $this->connection->exec($sql);
        }
    }
    
    private function insertSampleData() {
        // Insert appointment statuses
        $statuses = [
            ['Scheduled', '#007bff'],
            ['Confirmed', '#28a745'],
            ['Checked In', '#17a2b8'],
            ['In Progress', '#ffc107'],
            ['Completed', '#28a745'],
            ['Cancelled', '#dc3545'],
            ['No Show', '#6c757d'],
            ['Rescheduled', '#fd7e14']
        ];
        
        $stmt = $this->connection->prepare("INSERT INTO appointment_status (Status_Descr, color_code) VALUES (?, ?)");
        foreach ($statuses as $status) {
            $stmt->execute($status);
        }
        
        // Insert payment methods
        $methods = ['Cash', 'Credit Card', 'Debit Card', 'Insurance', 'Bank Transfer', 'Online Payment'];
        $stmt = $this->connection->prepare("INSERT INTO payment_methods (MethodName) VALUES (?)");
        foreach ($methods as $method) {
            $stmt->execute([$method]);
        }
        
        // Insert payment statuses
        $paymentStatuses = [
            ['Pending', '#ffc107'],
            ['Paid', '#28a745'],
            ['Failed', '#dc3545'],
            ['Refunded', '#17a2b8'],
            ['Partially Paid', '#fd7e14']
        ];
        
        $stmt = $this->connection->prepare("INSERT INTO payment_status (Status_Descr, color_code) VALUES (?, ?)");
        foreach ($paymentStatuses as $status) {
            $stmt->execute($status);
        }
        
        // Create admin user
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $this->connection->prepare("INSERT INTO users (username, email, password_hash, role, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@neohealth.com', $adminPassword, 'admin', 'System Administrator', '+1-555-0100']);
        
        // Create healthcare providers with authentic credentials
        $providers = [
            ['drsarahjohnson', 'sarah.johnson@neohealth.com', 'Dr. Sarah Johnson', '+1-555-0101', 'Cardiology', 'MD12345', 'Harvard Medical School - MD, Johns Hopkins - Cardiology Fellowship', 8],
            ['drmichaelchen', 'michael.chen@neohealth.com', 'Dr. Michael Chen', '+1-555-0102', 'Dermatology', 'MD12346', 'Stanford Medical School - MD, UCSF - Dermatology Residency', 12],
            ['dremilyrodriguez', 'emily.rodriguez@neohealth.com', 'Dr. Emily Rodriguez', '+1-555-0103', 'Pediatrics', 'MD12347', 'Yale Medical School - MD, Boston Children\'s Hospital - Pediatrics Residency', 6],
            ['drdavidkim', 'david.kim@neohealth.com', 'Dr. David Kim', '+1-555-0104', 'Orthopedics', 'MD12348', 'UCLA Medical School - MD, Mayo Clinic - Orthopedic Surgery Fellowship', 15],
            ['drlisathompson', 'lisa.thompson@neohealth.com', 'Dr. Lisa Thompson', '+1-555-0105', 'Neurology', 'MD12349', 'Columbia Medical School - MD, Mount Sinai - Neurology Fellowship', 10]
        ];
        
        $providerPassword = password_hash('provider123', PASSWORD_DEFAULT);
        foreach ($providers as $provider) {
            $stmt = $this->connection->prepare("INSERT INTO users (username, email, password_hash, role, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$provider[0], $provider[1], $providerPassword, 'provider', $provider[2], $provider[3]]);
            
            $userId = $this->connection->lastInsertId();
            $stmt = $this->connection->prepare("INSERT INTO providers (user_id, Prov_Name, Prov_Email, Prov_Phone, Prov_Spec, license_number, education, experience_years) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $provider[2], $provider[1], $provider[3], $provider[4], $provider[5], $provider[6], $provider[7]]);
        }
        
        // Create patients with realistic medical profiles
        $patients = [
            ['johnsmith', 'john.smith@email.com', 'John Smith', '+1-555-0201', '123 Main St, Boston, MA 02101', '1985-03-15', 'Jane Smith', '+1-555-0301', 'Hypertension managed with lisinopril 10mg daily, Type 2 diabetes controlled with metformin', 'Blue Cross Blue Shield', 'BC123456789'],
            ['mariagarcia', 'maria.garcia@email.com', 'Maria Garcia', '+1-555-0202', '456 Oak Ave, Los Angeles, CA 90210', '1992-07-22', 'Carlos Garcia', '+1-555-0302', 'No known allergies, annual wellness visits up to date', 'Aetna', 'AE987654321'],
            ['robertjohnson', 'robert.johnson@email.com', 'Robert Johnson', '+1-555-0203', '789 Pine St, Chicago, IL 60601', '1978-11-08', 'Mary Johnson', '+1-555-0303', 'Asthma - uses albuterol inhaler PRN, allergy to penicillin', 'Cigna', 'CG456789123'],
            ['jenniferbrown', 'jennifer.brown@email.com', 'Jennifer Brown', '+1-555-0204', '321 Elm Dr, Seattle, WA 98101', '1990-05-14', 'Michael Brown', '+1-555-0304', 'Chronic migraines, taking sumatriptan as needed', 'UnitedHealthcare', 'UH789123456'],
            ['williamdavis', 'william.davis@email.com', 'William Davis', '+1-555-0205', '654 Maple Ln, Miami, FL 33101', '1983-09-30', 'Susan Davis', '+1-555-0305', 'Hyperlipidemia, family history of coronary artery disease, taking atorvastatin', 'Kaiser Permanente', 'KP321654987']
        ];
        
        $patientPassword = password_hash('patient123', PASSWORD_DEFAULT);
        foreach ($patients as $patient) {
            $stmt = $this->connection->prepare("INSERT INTO users (username, email, password_hash, role, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$patient[0], $patient[1], $patientPassword, 'patient', $patient[2], $patient[3]]);
            
            $userId = $this->connection->lastInsertId();
            $stmt = $this->connection->prepare("INSERT INTO patients (user_id, Pat_Name, Pat_Email, Pat_Phone, Pat_Address, Pat_DOB, emergency_contact, emergency_phone, medical_history, insurance_provider, insurance_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $patient[2], $patient[1], $patient[3], $patient[4], $patient[5], $patient[6], $patient[7], $patient[8], $patient[9], $patient[10]]);
        }
        
        // Create realistic appointment schedule
        $appointments = [
            [1, 1, '2025-06-10 09:00:00', 45, 'Initial Consultation', 'Comprehensive cardiac evaluation for chest pain episodes', 2],
            [2, 2, '2025-06-10 14:30:00', 30, 'Follow-up', 'Dermatological assessment of suspicious mole', 1],
            [3, 3, '2025-06-11 11:00:00', 30, 'Well-child Visit', 'Annual pediatric examination with immunizations', 2],
            [4, 5, '2025-06-12 10:15:00', 45, 'Consultation', 'Neurological evaluation for chronic headaches', 3],
            [5, 4, '2025-06-13 15:30:00', 60, 'Follow-up', 'Orthopedic assessment for knee pain management', 1],
            [1, 2, '2025-06-14 16:00:00', 30, 'Consultation', 'Dermatology referral for skin condition', 1],
            [2, 1, '2025-06-17 08:30:00', 45, 'Follow-up', 'Cardiology follow-up for hypertension management', 2]
        ];
        
        foreach ($appointments as $apt) {
            $stmt = $this->connection->prepare("INSERT INTO appointments (Pat_ID, Prov_ID, DateTime, duration_minutes, appointment_type, notes, Status_ID) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute($apt);
        }
        
        // Add payment records for completed appointments
        $payments = [
            [1, 1, 185.00, 2, 2, 'TXN_CARD_001'],
            [3, 3, 125.00, 4, 2, 'TXN_INS_002'],
            [4, 4, 220.00, 1, 1, 'TXN_CASH_003'],
            [2, 2, 95.00, 6, 2, 'TXN_ONLINE_004']
        ];
        
        foreach ($payments as $payment) {
            $stmt = $this->connection->prepare("INSERT INTO payments (Pat_ID, Appt_ID, Amount, PaymentMeth_ID, PaymentStat_ID, transaction_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute($payment);
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage());
            throw new Exception("Database operation failed.");
        }
    }

    public function fetch($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Query fetch failed: " . $e->getMessage());
            throw new Exception("Database operation failed.");
        }
    }

    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query fetchAll failed: " . $e->getMessage());
            throw new Exception("Database operation failed.");
        }
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollback();
    }

    public function rowCount($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Row count failed: " . $e->getMessage());
            throw new Exception("Database operation failed.");
        }
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize a singleton.");
    }
}
?>
