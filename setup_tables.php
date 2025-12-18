<?php
// File untuk membuat semua tabel yang diperlukan
require_once 'koneksi.php';

echo "<h2>Setup Database Daily Journal</h2><hr>";

// 1. Tabel users (sudah ada dari sebelumnya)
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_users) === TRUE) {
    echo "✓ Tabel 'users' berhasil dibuat/sudah ada!<br>";
    
    // Insert user default
    $default_username = "admin";
    $default_password = password_hash("admin123", PASSWORD_DEFAULT);
    $default_email = "admin@dailyjournal.com";
    $default_fullname = "Administrator";
    
    // Hapus user lama jika ada
    $delete_sql = "DELETE FROM users WHERE username = '$default_username'";
    $conn->query($delete_sql);
    
    // Insert user baru dengan prepared statement
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $default_username, $default_password, $default_email, $default_fullname);
    
    if ($stmt->execute()) {
        echo "&nbsp;&nbsp;✓ User default berhasil dibuat! (admin/admin123)<br>";
        echo "&nbsp;&nbsp;<small>Password hash: " . substr($default_password, 0, 20) . "...</small><br>";
    } else {
        echo "&nbsp;&nbsp;✗ Error: " . $stmt->error . "<br>";
    }
    $stmt->close();
} else {
    echo "✗ Error membuat tabel users: " . $conn->error . "<br>";
}

// 2. Tabel articles
$sql_articles = "CREATE TABLE IF NOT EXISTS articles (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(50) DEFAULT 'General',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql_articles) === TRUE) {
    echo "✓ Tabel 'articles' berhasil dibuat/sudah ada!<br>";
} else {
    echo "✗ Error membuat tabel articles: " . $conn->error . "<br>";
}

// 3. Tabel diary
$sql_diary = "CREATE TABLE IF NOT EXISTS diary (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    mood VARCHAR(50) DEFAULT 'Normal',
    diary_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql_diary) === TRUE) {
    echo "✓ Tabel 'diary' berhasil dibuat/sudah ada!<br>";
} else {
    echo "✗ Error membuat tabel diary: " . $conn->error . "<br>";
}

// 4. Tabel gallery
$sql_gallery = "CREATE TABLE IF NOT EXISTS gallery (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql_gallery) === TRUE) {
    echo "✓ Tabel 'gallery' berhasil dibuat/sudah ada!<br>";
} else {
    echo "✗ Error membuat tabel gallery: " . $conn->error . "<br>";
}

// Buat folder uploads jika belum ada
$upload_dir = 'uploads';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
    echo "✓ Folder 'uploads' berhasil dibuat!<br>";
} else {
    echo "✓ Folder 'uploads' sudah ada!<br>";
}

echo "<hr>";
echo "<h3>Setup Selesai!</h3>";
echo "<p>Semua tabel berhasil dibuat. Anda dapat login dengan:</p>";
echo "<ul>";
echo "<li><strong>Username:</strong> admin</li>";
echo "<li><strong>Password:</strong> admin123</li>";
echo "</ul>";
echo "<br><a href='login.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Ke Halaman Login</a>";
echo " <a href='dashboard.php' style='padding: 10px 20px; background: #764ba2; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Ke Dashboard</a>";

$conn->close();
?>
