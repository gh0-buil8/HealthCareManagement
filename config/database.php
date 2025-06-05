<?php
class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $port;
    private $pdo;
    
    public function __construct() {
        // Use environment variables for PostgreSQL connection
        $this->host = $_ENV['PGHOST'] ?? getenv('PGHOST') ?? 'localhost';
        $this->username = $_ENV['PGUSER'] ?? getenv('PGUSER') ?? 'postgres';
        $this->password = $_ENV['PGPASSWORD'] ?? getenv('PGPASSWORD') ?? '';
        $this->database = $_ENV['PGDATABASE'] ?? getenv('PGDATABASE') ?? 'healthcare_ams';
        $this->port = $_ENV['PGPORT'] ?? getenv('PGPORT') ?? '5432';
        
        $this->connect();
        $this->initializeSchema();
    }
    
    private function connect() {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function initializeSchema() {
        try {
            // Create tables if they don't exist
            $this->createUsersTable();
            $this->createPatientsTable();
            $this->createProvidersTable();
            $this->createAppointmentStatusTable();
            $this->createAppointmentsTable();
            $this->createPaymentMethodsTable();
            $this->createPaymentStatusTable();
            $this->createPaymentsTable();
            $this->createNotificationsTable();
            
            // Insert initial data
            $this->insertInitialData();
        } catch (Exception $e) {
            error_log("Schema initialization error: " . $e->getMessage());
        }
    }
    
    private function createUsersTable() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            user_id SERIAL PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(150) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(50) NOT NULL DEFAULT 'patient',
            full_name VARCHAR(200) NOT NULL,
            phone VARCHAR(20),
            is_active BOOLEAN DEFAULT true,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);
    }
    
    private function createPatientsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS patients (
            pat_id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(user_id) ON DELETE CASCADE,
            pat_name VARCHAR(200) NOT NULL,
            pat_email VARCHAR(150) UNIQUE NOT NULL,
            pat_phone VARCHAR(20),
            pat_address TEXT,
            pat_dob DATE,
            emergency_contact VARCHAR(200),
            emergency_phone VARCHAR(20),
            medical_history TEXT,
            insurance_provider VARCHAR(100),
            insurance_number VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);
    }
    
    private function createProvidersTable() {
        $sql = "CREATE TABLE IF NOT EXISTS providers (
            prov_id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(user_id) ON DELETE CASCADE,
            prov_name VARCHAR(200) NOT NULL,
            prov_email VARCHAR(150) UNIQUE NOT NULL,
            prov_phone VARCHAR(20),
            prov_spec VARCHAR(100) NOT NULL,
            license_number VARCHAR(100),
            education TEXT,
            experience_years INTEGER DEFAULT 0,
            available_days VARCHAR(50) DEFAULT 'Mon,Tue,Wed,Thu,Fri',
            available_start_time TIME DEFAULT '09:00:00',
            available_end_time TIME DEFAULT '17:00:00',
            consultation_fee DECIMAL(10,2) DEFAULT 100.00,
            is_accepting_patients BOOLEAN DEFAULT true,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);
    }
    
    private function createAppointmentStatusTable() {
        $sql = "CREATE TABLE IF NOT EXISTS appointment_status (
            status_id SERIAL PRIMARY KEY,
            status_descr VARCHAR(50) UNIQUE NOT NULL,
            color_code VARCHAR(7) DEFAULT '#007bff'
        )";
        $this->pdo->exec($sql);
    }
    
    private function createAppointmentsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS appointments (
            appt_id SERIAL PRIMARY KEY,
            pat_id INTEGER REFERENCES patients(pat_id) ON DELETE CASCADE,
            prov_id INTEGER REFERENCES providers(prov_id) ON DELETE CASCADE,
            datetime TIMESTAMP NOT NULL,
            duration_minutes INTEGER DEFAULT 30,
            appointment_type VARCHAR(100) DEFAULT 'Consultation',
            notes TEXT,
            status_id INTEGER REFERENCES appointment_status(status_id) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);
    }
    
    private function createPaymentMethodsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS payment_methods (
            paymentmeth_id SERIAL PRIMARY KEY,
            methodname VARCHAR(50) UNIQUE NOT NULL,
            is_active BOOLEAN DEFAULT true
        )";
        $this->pdo->exec($sql);
    }
    
    private function createPaymentStatusTable() {
        $sql = "CREATE TABLE IF NOT EXISTS payment_status (
            paymentstat_id SERIAL PRIMARY KEY,
            status_descr VARCHAR(50) UNIQUE NOT NULL,
            color_code VARCHAR(7) DEFAULT '#007bff'
        )";
        $this->pdo->exec($sql);
    }
    
    private function createPaymentsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS payments (
            payment_id SERIAL PRIMARY KEY,
            pat_id INTEGER REFERENCES patients(pat_id) ON DELETE CASCADE,
            appt_id INTEGER REFERENCES appointments(appt_id) ON DELETE SET NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            paymentmeth_id INTEGER REFERENCES payment_methods(paymentmeth_id),
            paymentstat_id INTEGER REFERENCES payment_status(paymentstat_id) DEFAULT 1,
            transaction_id VARCHAR(100),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);
    }
    
    private function createNotificationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS notifications (
            notif_id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(user_id) ON DELETE CASCADE,
            title VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) DEFAULT 'info',
            is_read BOOLEAN DEFAULT false,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);
    }
    
    private function insertInitialData() {
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

        $stmt = $this->pdo->prepare("INSERT INTO appointment_status (status_descr, color_code) VALUES (?, ?) ON CONFLICT (status_descr) DO NOTHING");
        foreach ($statuses as $status) {
            $stmt->execute($status);
        }

        // Insert payment methods
        $methods = ['Cash', 'Credit Card', 'Debit Card', 'Insurance', 'Bank Transfer', 'Online Payment'];
        $stmt = $this->pdo->prepare("INSERT INTO payment_methods (methodname) VALUES (?) ON CONFLICT (methodname) DO NOTHING");
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

        $stmt = $this->pdo->prepare("INSERT INTO payment_status (status_descr, color_code) VALUES (?, ?) ON CONFLICT (status_descr) DO NOTHING");
        foreach ($paymentStatuses as $status) {
            $stmt->execute($status);
        }

        // Insert sample admin user
        $this->insertSampleUsers();
    }
    
    private function insertSampleUsers() {
        // Check if admin exists
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $adminExists = $stmt->fetchColumn() > 0;
        
        if (!$adminExists) {
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password_hash, role, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['admin', 'admin@neohealth.com', $adminPassword, 'admin', 'System Administrator', '+1-555-0100']);
        }

        // Insert sample providers
        $providers = [
            ['Dr. Sarah Johnson', 'sarah.johnson@neohealth.com', '+1-555-0101', 'Cardiology', 'MD12345', 'Harvard Medical School - MD, Johns Hopkins - Cardiology Fellowship', 8],
            ['Dr. Michael Chen', 'michael.chen@neohealth.com', '+1-555-0102', 'Dermatology', 'MD12346', 'Stanford Medical School - MD, UCSF - Dermatology Residency', 12],
            ['Dr. Emily Rodriguez', 'emily.rodriguez@neohealth.com', '+1-555-0103', 'Pediatrics', 'MD12347', 'Yale Medical School - MD, Children\'s Hospital - Pediatrics Residency', 6],
            ['Dr. David Kim', 'david.kim@neohealth.com', '+1-555-0104', 'Orthopedics', 'MD12348', 'UCLA Medical School - MD, Mayo Clinic - Orthopedic Surgery Fellowship', 15],
            ['Dr. Lisa Thompson', 'lisa.thompson@neohealth.com', '+1-555-0105', 'Neurology', 'MD12349', 'Columbia Medical School - MD, Mount Sinai - Neurology Fellowship', 10]
        ];

        foreach ($providers as $provider) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$provider[1]]);
            if ($stmt->fetchColumn() == 0) {
                $providerPassword = password_hash('provider123', PASSWORD_DEFAULT);
                $username = strtolower(str_replace([' ', '.'], ['', ''], $provider[0]));
                
                // Insert user
                $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password_hash, role, full_name, phone) VALUES (?, ?, ?, ?, ?, ?) RETURNING user_id");
                $stmt->execute([$username, $provider[1], $providerPassword, 'provider', $provider[0], $provider[2]]);
                $result = $stmt->fetch();
                
                if ($result) {
                    $userId = $result['user_id'];
                    
                    // Insert provider details
                    $stmt = $this->pdo->prepare("INSERT INTO providers (user_id, prov_name, prov_email, prov_phone, prov_spec, license_number, education, experience_years) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$userId, $provider[0], $provider[1], $provider[2], $provider[3], $provider[4], $provider[5], $provider[6]]);
                }
            }
        }

        // Insert sample patients
        $patients = [
            ['John Smith', 'john.smith@email.com', '+1-555-0201', '123 Main St, Anytown, ST 12345', '1985-03-15', 'Jane Smith', '+1-555-0301', 'Hypertension, Diabetes Type 2', 'Blue Cross Blue Shield', 'BC123456789'],
            ['Maria Garcia', 'maria.garcia@email.com', '+1-555-0202', '456 Oak Ave, Somewhere, ST 12346', '1992-07-22', 'Carlos Garcia', '+1-555-0302', 'No significant medical history', 'Aetna', 'AE987654321'],
            ['Robert Johnson', 'robert.johnson@email.com', '+1-555-0203', '789 Pine St, Elsewhere, ST 12347', '1978-11-08', 'Mary Johnson', '+1-555-0303', 'Asthma, Allergies to penicillin', 'Cigna', 'CG456789123'],
            ['Jennifer Brown', 'jennifer.brown@email.com', '+1-555-0204', '321 Elm Dr, Nowhere, ST 12348', '1990-05-14', 'Michael Brown', '+1-555-0304', 'Migraine headaches', 'UnitedHealthcare', 'UH789123456'],
            ['William Davis', 'william.davis@email.com', '+1-555-0205', '654 Maple Ln, Anyplace, ST 12349', '1983-09-30', 'Susan Davis', '+1-555-0305', 'High cholesterol, Family history of heart disease', 'Kaiser Permanente', 'KP321654987']
        ];

        foreach ($patients as $patient) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$patient[1]]);
            if ($stmt->fetchColumn() == 0) {
                $patientPassword = password_hash('patient123', PASSWORD_DEFAULT);
                $username = strtolower(str_replace([' ', '.'], ['', ''], $patient[0]));
                
                // Insert user
                $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password_hash, role, full_name, phone) VALUES (?, ?, ?, ?, ?, ?) RETURNING user_id");
                $stmt->execute([$username, $patient[1], $patientPassword, 'patient', $patient[0], $patient[2]]);
                $result = $stmt->fetch();
                
                if ($result) {
                    $userId = $result['user_id'];
                    
                    // Insert patient details
                    $stmt = $this->pdo->prepare("INSERT INTO patients (user_id, pat_name, pat_email, pat_phone, pat_address, pat_dob, emergency_contact, emergency_phone, medical_history, insurance_provider, insurance_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$userId, $patient[0], $patient[1], $patient[2], $patient[3], $patient[4], $patient[5], $patient[6], $patient[7], $patient[8], $patient[9]]);
                }
            }
        }
        
        // Insert sample appointments with real data
        $this->insertSampleAppointments();
    }
    
    private function insertSampleAppointments() {
        // Check if appointments already exist
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM appointments");
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) return;
        
        // Get patient and provider IDs
        $patients = $this->fetchAll("SELECT pat_id FROM patients LIMIT 5");
        $providers = $this->fetchAll("SELECT prov_id FROM providers LIMIT 5");
        
        if (empty($patients) || empty($providers)) return;
        
        // Create appointments for the next 30 days
        $appointments = [];
        $startDate = new DateTime();
        
        for ($i = 1; $i <= 20; $i++) {
            $appointmentDate = clone $startDate;
            $appointmentDate->add(new DateInterval('P' . rand(1, 30) . 'D'));
            $appointmentDate->setTime(rand(9, 16), rand(0, 3) * 15); // 9 AM to 4 PM, 15-min intervals
            
            $patientId = $patients[array_rand($patients)]['pat_id'];
            $providerId = $providers[array_rand($providers)]['prov_id'];
            $statusId = rand(1, 5); // Random status
            
            $appointments[] = [
                $patientId,
                $providerId,
                $appointmentDate->format('Y-m-d H:i:s'),
                rand(30, 60), // Duration 30-60 minutes
                ['Consultation', 'Follow-up', 'Checkup', 'Treatment'][array_rand(['Consultation', 'Follow-up', 'Checkup', 'Treatment'])],
                'Regular appointment scheduled',
                $statusId
            ];
        }
        
        $stmt = $this->pdo->prepare("INSERT INTO appointments (pat_id, prov_id, datetime, duration_minutes, appointment_type, notes, status_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($appointments as $appointment) {
            $stmt->execute($appointment);
        }
        
        // Insert sample payments
        $this->insertSamplePayments();
    }
    
    private function insertSamplePayments() {
        // Get completed appointments
        $appointments = $this->fetchAll("SELECT appt_id, pat_id FROM appointments WHERE status_id = 5 LIMIT 10"); // Completed
        
        foreach ($appointments as $appointment) {
            $amount = rand(50, 300); // Random amount between $50-$300
            $methodId = rand(1, 6); // Random payment method
            $statusId = rand(1, 2); // Pending or Paid
            
            $stmt = $this->pdo->prepare("INSERT INTO payments (pat_id, appt_id, amount, paymentmeth_id, paymentstat_id, transaction_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $appointment['pat_id'],
                $appointment['appt_id'],
                $amount,
                $methodId,
                $statusId,
                'TXN' . strtoupper(uniqid())
            ]);
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }
    
    public function execute($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}
?>
