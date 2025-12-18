<?php
date_default_timezone_set('Asia/Jakarta');

// Deteksi environment - Railway menggunakan MYSQL* variables
$is_production = getenv('MYSQLHOST') !== false;

if ($is_production) {
    // Konfigurasi Railway MySQL (Production)
    // Railway secara otomatis inject environment variables ini
    $servername = getenv('MYSQLHOST');
    $username = getenv('MYSQLUSER');
    $password = getenv('MYSQLPASSWORD');
    $db = getenv('MYSQLDATABASE');
    $port = (int)getenv('MYSQLPORT');
    
    // Create connection dengan port untuk Railway
    $conn = new mysqli($servername, $username, $password, $db, $port);
} else {
    // Konfigurasi Local (XAMPP)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $db = "webdailyjournal";
    
    // Create connection local
    $conn = new mysqli($servername, $username, $password, $db);
}

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>
