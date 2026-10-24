<?php
$host = "localhost";
$dbname = "studenthub";
$username = "root";
$password = "";

// MySQLi connection (Practical 8, 9, 11)
$conn = @mysqli_connect($host, $username, $password, $dbname);
if (!$conn) {
    // If database doesn't exist yet, connect without db to allow setup
    $conn = @mysqli_connect($host, $username, $password);
}

// PDO connection (Practical 8, 10, 12, 13, 14)
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback if db isn't created yet
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e2) {
        die("Database connection failed. Please ensure MySQL is running in XAMPP.");
    }
}
?>
