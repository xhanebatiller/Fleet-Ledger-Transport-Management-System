<?php
// connection.php
$host = "localhost";
$username = "u174213183_root";
$password = "Pcl123456789";
$dbname = "u174213183_pcldb";

// Define constants for PDO connections
define('DB_SERVER', $host);
define('DB_USERNAME', $username);
define('DB_PASSWORD', $password);
define('DB_NAME', $dbname);

// Create mysqli connection (for legacy code)
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    throw new Exception("Database connection error", 500);
}

$conn->set_charset("utf8mb4");

// Create PDO connection (for newer code)
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("PDO Database connection failed: " . $e->getMessage());
    throw new Exception("Database connection error", 500);
}
?>