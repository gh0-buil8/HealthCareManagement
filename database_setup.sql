-- Healthcare AMS Database Schema for MySQL/XAMPP
-- Create Database
CREATE DATABASE IF NOT EXISTS healthcare_ams CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE healthcare_ams;

-- Users table
CREATE TABLE IF NOT EXISTS users (
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
);

-- Patients table
CREATE TABLE IF NOT EXISTS patients (
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Providers table
CREATE TABLE IF NOT EXISTS providers (
    Prov_ID INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    Prov_Name VARCHAR(200) NOT NULL,
    Prov_Email VARCHAR(150) UNIQUE NOT NULL,
    Prov_Phone VARCHAR(20),
    Prov_Spec VARCHAR(100) NOT NULL,
    license_number VARCHAR(100),
    education TEXT,
    experience_years INT DEFAULT 0,
    available_days VARCHAR(50) DEFAULT 'Mon,Tue,Wed,Thu,Fri',
    available_start_time TIME DEFAULT '09:00:00',
    available_end_time TIME DEFAULT '17:00:00',
    consultation_fee DECIMAL(10,2) DEFAULT 100.00,
    is_accepting_patients BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Appointment Status table
CREATE TABLE IF NOT EXISTS appointment_status (
    Status_ID INT AUTO_INCREMENT PRIMARY KEY,
    Status_Descr VARCHAR(50) UNIQUE NOT NULL,
    color_code VARCHAR(7) DEFAULT '#007bff'
);

-- Appointments table
CREATE TABLE IF NOT EXISTS appointments (
    Appt_ID INT AUTO_INCREMENT PRIMARY KEY,
    Pat_ID INT NOT NULL,
    Prov_ID INT NOT NULL,
    DateTime DATETIME NOT NULL,
    duration_minutes INT DEFAULT 30,
    appointment_type VARCHAR(100) DEFAULT 'Consultation',
    notes TEXT,
    Status_ID INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (Pat_ID) REFERENCES patients(Pat_ID) ON DELETE CASCADE,
    FOREIGN KEY (Prov_ID) REFERENCES providers(Prov_ID) ON DELETE CASCADE,
    FOREIGN KEY (Status_ID) REFERENCES appointment_status(Status_ID)
);

-- Payment Methods table
CREATE TABLE IF NOT EXISTS payment_methods (
    PaymentMeth_ID INT AUTO_INCREMENT PRIMARY KEY,
    MethodName VARCHAR(50) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Payment Status table
CREATE TABLE IF NOT EXISTS payment_status (
    PaymentStat_ID INT AUTO_INCREMENT PRIMARY KEY,
    Status_Descr VARCHAR(50) UNIQUE NOT NULL,
    color_code VARCHAR(7) DEFAULT '#007bff'
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    Payment_ID INT AUTO_INCREMENT PRIMARY KEY,
    Pat_ID INT NOT NULL,
    Appt_ID INT,
    Amount DECIMAL(10,2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PaymentMeth_ID INT,
    PaymentStat_ID INT DEFAULT 1,
    transaction_id VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Pat_ID) REFERENCES patients(Pat_ID) ON DELETE CASCADE,
    FOREIGN KEY (Appt_ID) REFERENCES appointments(Appt_ID) ON DELETE SET NULL,
    FOREIGN KEY (PaymentMeth_ID) REFERENCES payment_methods(PaymentMeth_ID),
    FOREIGN KEY (PaymentStat_ID) REFERENCES payment_status(PaymentStat_ID)
);

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    Notif_ID INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Insert initial data
-- Appointment statuses
INSERT IGNORE INTO appointment_status (Status_Descr, color_code) VALUES
('Scheduled', '#007bff'),
('Confirmed', '#28a745'),
('Checked In', '#17a2b8'),
('In Progress', '#ffc107'),
('Completed', '#28a745'),
('Cancelled', '#dc3545'),
('No Show', '#6c757d'),
('Rescheduled', '#fd7e14');

-- Payment methods
INSERT IGNORE INTO payment_methods (MethodName) VALUES
('Cash'),
('Credit Card'),
('Debit Card'),
('Insurance'),
('Bank Transfer'),
('Online Payment');

-- Payment statuses
INSERT IGNORE INTO payment_status (Status_Descr, color_code) VALUES
('Pending', '#ffc107'),
('Paid', '#28a745'),
('Failed', '#dc3545'),
('Refunded', '#17a2b8'),
('Partially Paid', '#fd7e14');

-- Sample admin user (password: admin123)
INSERT IGNORE INTO users (username, email, password_hash, role, full_name, phone) VALUES
('admin', 'admin@neohealth.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', '+1-555-0100');

-- Sample providers
INSERT IGNORE INTO users (username, email, password_hash, role, full_name, phone) VALUES
('drsarahjohnson', 'sarah.johnson@neohealth.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'provider', 'Dr. Sarah Johnson', '+1-555-0101'),
('drmichaelchen', 'michael.chen@neohealth.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'provider', 'Dr. Michael Chen', '+1-555-0102'),
('dremilyrodriguez', 'emily.rodriguez@neohealth.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'provider', 'Dr. Emily Rodriguez', '+1-555-0103'),
('drdavidkim', 'david.kim@neohealth.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'provider', 'Dr. David Kim', '+1-555-0104'),
('drlisathompson', 'lisa.thompson@neohealth.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'provider', 'Dr. Lisa Thompson', '+1-555-0105');

-- Sample patients
INSERT IGNORE INTO users (username, email, password_hash, role, full_name, phone) VALUES
('johnsmith', 'john.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', 'John Smith', '+1-555-0201'),
('mariagarcia', 'maria.garcia@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', 'Maria Garcia', '+1-555-0202'),
('robertjohnson', 'robert.johnson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', 'Robert Johnson', '+1-555-0203'),
('jenniferbrown', 'jennifer.brown@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', 'Jennifer Brown', '+1-555-0204'),
('williamdavis', 'william.davis@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', 'William Davis', '+1-555-0205');

-- Insert provider details
INSERT IGNORE INTO providers (user_id, Prov_Name, Prov_Email, Prov_Phone, Prov_Spec, license_number, education, experience_years) 
SELECT user_id, full_name, email, phone, 'Cardiology', 'MD12345', 'Harvard Medical School - MD, Johns Hopkins - Cardiology Fellowship', 8 
FROM users WHERE email = 'sarah.johnson@neohealth.com';

INSERT IGNORE INTO providers (user_id, Prov_Name, Prov_Email, Prov_Phone, Prov_Spec, license_number, education, experience_years) 
SELECT user_id, full_name, email, phone, 'Dermatology', 'MD12346', 'Stanford Medical School - MD, UCSF - Dermatology Residency', 12 
FROM users WHERE email = 'michael.chen@neohealth.com';

INSERT IGNORE INTO providers (user_id, Prov_Name, Prov_Email, Prov_Phone, Prov_Spec, license_number, education, experience_years) 
SELECT user_id, full_name, email, phone, 'Pediatrics', 'MD12347', 'Yale Medical School - MD, Children\'s Hospital - Pediatrics Residency', 6 
FROM users WHERE email = 'emily.rodriguez@neohealth.com';

INSERT IGNORE INTO providers (user_id, Prov_Name, Prov_Email, Prov_Phone, Prov_Spec, license_number, education, experience_years) 
SELECT user_id, full_name, email, phone, 'Orthopedics', 'MD12348', 'UCLA Medical School - MD, Mayo Clinic - Orthopedic Surgery Fellowship', 15 
FROM users WHERE email = 'david.kim@neohealth.com';

INSERT IGNORE INTO providers (user_id, Prov_Name, Prov_Email, Prov_Phone, Prov_Spec, license_number, education, experience_years) 
SELECT user_id, full_name, email, phone, 'Neurology', 'MD12349', 'Columbia Medical School - MD, Mount Sinai - Neurology Fellowship', 10 
FROM users WHERE email = 'lisa.thompson@neohealth.com';

-- Insert patient details
INSERT IGNORE INTO patients (user_id, Pat_Name, Pat_Email, Pat_Phone, Pat_Address, Pat_DOB, emergency_contact, emergency_phone, medical_history, insurance_provider, insurance_number) 
SELECT user_id, full_name, email, phone, '123 Main St, Anytown, ST 12345', '1985-03-15', 'Jane Smith', '+1-555-0301', 'Hypertension, Diabetes Type 2', 'Blue Cross Blue Shield', 'BC123456789' 
FROM users WHERE email = 'john.smith@email.com';

INSERT IGNORE INTO patients (user_id, Pat_Name, Pat_Email, Pat_Phone, Pat_Address, Pat_DOB, emergency_contact, emergency_phone, medical_history, insurance_provider, insurance_number) 
SELECT user_id, full_name, email, phone, '456 Oak Ave, Somewhere, ST 12346', '1992-07-22', 'Carlos Garcia', '+1-555-0302', 'No significant medical history', 'Aetna', 'AE987654321' 
FROM users WHERE email = 'maria.garcia@email.com';

INSERT IGNORE INTO patients (user_id, Pat_Name, Pat_Email, Pat_Phone, Pat_Address, Pat_DOB, emergency_contact, emergency_phone, medical_history, insurance_provider, insurance_number) 
SELECT user_id, full_name, email, phone, '789 Pine St, Elsewhere, ST 12347', '1978-11-08', 'Mary Johnson', '+1-555-0303', 'Asthma, Allergies to penicillin', 'Cigna', 'CG456789123' 
FROM users WHERE email = 'robert.johnson@email.com';

INSERT IGNORE INTO patients (user_id, Pat_Name, Pat_Email, Pat_Phone, Pat_Address, Pat_DOB, emergency_contact, emergency_phone, medical_history, insurance_provider, insurance_number) 
SELECT user_id, full_name, email, phone, '321 Elm Dr, Nowhere, ST 12348', '1990-05-14', 'Michael Brown', '+1-555-0304', 'Migraine headaches', 'UnitedHealthcare', 'UH789123456' 
FROM users WHERE email = 'jennifer.brown@email.com';

INSERT IGNORE INTO patients (user_id, Pat_Name, Pat_Email, Pat_Phone, Pat_Address, Pat_DOB, emergency_contact, emergency_phone, medical_history, insurance_provider, insurance_number) 
SELECT user_id, full_name, email, phone, '654 Maple Ln, Anyplace, ST 12349', '1983-09-30', 'Susan Davis', '+1-555-0305', 'High cholesterol, Family history of heart disease', 'Kaiser Permanente', 'KP321654987' 
FROM users WHERE email = 'william.davis@email.com';

-- Sample appointments
INSERT INTO appointments (Pat_ID, Prov_ID, DateTime, duration_minutes, appointment_type, notes, Status_ID) VALUES
(1, 1, '2025-06-10 09:00:00', 45, 'Consultation', 'Initial consultation for cardiac evaluation', 2),
(2, 2, '2025-06-10 10:30:00', 30, 'Follow-up', 'Skin condition follow-up', 1),
(3, 3, '2025-06-11 14:00:00', 30, 'Checkup', 'Annual pediatric checkup', 2),
(4, 4, '2025-06-12 11:00:00', 60, 'Treatment', 'Physical therapy assessment', 3),
(5, 5, '2025-06-13 15:30:00', 45, 'Consultation', 'Neurological evaluation', 1),
(1, 2, '2025-06-15 16:00:00', 30, 'Follow-up', 'Dermatology consultation', 1),
(3, 1, '2025-06-16 08:30:00', 45, 'Consultation', 'Heart health screening', 2),
(2, 5, '2025-06-17 13:00:00', 30, 'Consultation', 'Headache evaluation', 1);

-- Sample payments
INSERT INTO payments (Pat_ID, Appt_ID, Amount, PaymentMeth_ID, PaymentStat_ID, transaction_id) VALUES
(1, 1, 150.00, 2, 2, 'TXN001'),
(2, 2, 100.00, 1, 2, 'TXN002'),
(3, 3, 120.00, 4, 1, 'TXN003'),
(4, 4, 200.00, 2, 2, 'TXN004'),
(5, 5, 175.00, 3, 1, 'TXN005');