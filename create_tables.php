<?php
// File untuk membuat tabel users
require_once 'koneksi.php';

// SQL untuk membuat tabel users
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabel 'users' berhasil dibuat!<br>";
    
    // Insert user default untuk testing
    // Password: admin123 (sudah di-hash)
    $default_username = "admin";
    $default_password = password_hash("admin123", PASSWORD_DEFAULT);
    $default_email = "admin@dailyjournal.com";
    $default_fullname = "Administrator";
    
    $check_sql = "SELECT * FROM users WHERE username = '$default_username'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows == 0) {
        $insert_sql = "INSERT INTO users (username, password, email, full_name) 
                       VALUES ('$default_username', '$default_password', '$default_email', '$default_fullname')";
        
        if ($conn->query($insert_sql) === TRUE) {
            echo "User default berhasil dibuat!<br>";
            echo "<strong>Username:</strong> admin<br>";
            echo "<strong>Password:</strong> admin123<br>";
        } else {
            echo "Error membuat user default: " . $conn->error;
        }
    } else {
        echo "User default sudah ada.<br>";
    }
} else {
    echo "Error membuat tabel: " . $conn->error;
}

// SQL untuk membuat tabel articles (untuk daily journal)
$sql_articles = "CREATE TABLE IF NOT EXISTS articles (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql_articles) === TRUE) {
    echo "Tabel 'articles' berhasil dibuat!<br>";
} else {
    echo "Error membuat tabel articles: " . $conn->error;
}

echo "<br><a href='login.php'>Kembali ke Login</a>";

$conn->close();
?>
