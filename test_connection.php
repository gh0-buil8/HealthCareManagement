<?php
// Simple database connection test for XAMPP
try {
    $host = 'localhost';
    $username = 'root';
    $password = 'test1234';
    $database = 'healthcare_ams';
    
    // First test: Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to MySQL server successfully<br>";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '$database' ready<br>";
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to healthcare_ams database<br>";
    
    // Test query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✓ Database query test successful<br>";
    
    echo "<br><strong>Database connection is working properly!</strong><br>";
    echo "You can now use the authentication system.";
    
} catch (PDOException $e) {
    echo " Database connection failed: " . $e->getMessage() . "<br>";
    echo "<br>Please check:<br>";
    echo "1. XAMPP MySQL service is running<br>";
    echo "2. MySQL password is 'test1234' for root user<br>";
    echo "3. No firewall blocking the connection<br>";
}
?>