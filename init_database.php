<?php
// Database initialization script for XAMPP MySQL
require_once 'config/config.php';

try {
    // Connect to MySQL server without specifying database
    $pdo = new PDO("mysql:host=localhost", "root", "test1234");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS healthcare_ams CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database 'healthcare_ams' created successfully.<br>";
    
    // Use the database
    $pdo->exec("USE healthcare_ams");
    
    // Read and execute the SQL file
    $sql = file_get_contents('database_setup.sql');
    
    // Split into individual statements
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "Database schema and sample data initialized successfully!<br>";
    echo "You can now use the Healthcare AMS with the following test accounts:<br><br>";
    echo "<strong>Admin:</strong> admin@neohealth.com / admin123<br>";
    echo "<strong>Provider:</strong> sarah.johnson@neohealth.com / provider123<br>";
    echo "<strong>Patient:</strong> john.smith@email.com / patient123<br>";
    
} catch (PDOException $e) {
    die("Database initialization failed: " . $e->getMessage());
}
?>