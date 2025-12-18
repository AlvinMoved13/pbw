<?php
date_default_timezone_set('Asia/Jakarta');

// Deteksi environment (local atau production)
$is_production = isset($_ENV['RAILWAY_ENVIRONMENT']) || isset($_ENV['VERCEL']) || getenv('MYSQLHOST');

if ($is_production) {
    // Konfigurasi Railway MySQL (Production)
    // Ganti dengan kredensial Railway Anda setelah setup
    $servername = getenv('MYSQLHOST') ?: 'junction.proxy.rlwy.net';
    $username = getenv('MYSQLUSER') ?: 'root';
    $password = getenv('MYSQLPASSWORD') ?: '';
    $db = getenv('MYSQLDATABASE') ?: 'railway';
    $port = getenv('MYSQLPORT') ?: 3306;
    
    // Create connection dengan port
    $conn = new mysqli($servername, $username, $password, $db, $port);
} else {
    // Konfigurasi Local (XAMPP)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $db = "webdailyjournal";
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $db);
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>
