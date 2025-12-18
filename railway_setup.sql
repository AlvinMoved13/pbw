-- Script untuk setup database Railway MySQL
-- Jalankan di Railway MySQL via "Connect" button

-- 1. Create database (jika perlu)
CREATE DATABASE IF NOT EXISTS railway CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE railway;

-- 2. Drop tables jika sudah ada (untuk fresh install)
DROP TABLE IF EXISTS gallery;
DROP TABLE IF EXISTS diary;
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS users;

-- 3. Create table users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Create table articles
CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Create table diary
CREATE TABLE diary (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    mood VARCHAR(50) DEFAULT 'Normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create table gallery
CREATE TABLE gallery (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_upload_date (upload_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');

-- 8. Insert sample articles
INSERT INTO articles (user_id, title, content, category, created_at) VALUES
(1, 'Manfaat Menulis Setiap Hari', 'Menulis setiap hari dapat membantu meningkatkan kemampuan berpikir, memperbaiki konsentrasi, dan mengekspresikan diri dengan lebih baik.', 'Personal', '2025-02-01 09:00:00'),
(1, 'Rahasia Produktivitas di Pagi Hari', 'Memulai hari lebih awal, membuat to-do list, dan menghindari distraksi adalah kunci produktivitas yang efektif.', 'Productivity', '2025-02-02 08:45:00'),
(1, 'Cara Menjaga Kesehatan Mental', 'Istirahat cukup, journaling, berbicara dengan orang terdekat, dan melakukan hobi dapat menjaga kesehatan mental tetap stabil.', 'Health', '2025-02-03 14:12:00'),
(1, 'Tips Menyusun Tujuan Hidup', 'Membuat tujuan yang spesifik, terukur, realistis, dan berbatas waktu dapat membantu mencapai impian lebih cepat.', 'Motivation', '2025-02-04 11:33:00'),
(1, 'Mengapa Membaca Itu Penting?', 'Membaca dapat membuka wawasan baru, melatih imajinasi, dan meningkatkan kemampuan berpikir kritis.', 'Education', '2025-02-05 19:20:00');

-- 9. Insert sample diary entries
INSERT INTO diary (user_id, title, content, mood, created_at) VALUES
(1, 'Hari yang Produktif', 'Hari ini aku bisa menyelesaikan semua tugas yang tertunda. Rasanya sangat lega dan bangga.', 'Happy', '2025-01-10 20:15:00'),
(1, 'Pagi yang Tenang', 'Aku bangun lebih awal dari biasanya. Udara pagi sangat segar dan membuat suasana hati membaik.', 'Peaceful', '2025-01-11 07:20:00'),
(1, 'Sedikit Lelah', 'Hari ini cukup melelahkan karena banyak aktivitas. Tapi tetap bersyukur semuanya berjalan baik.', 'Normal', '2025-01-12 21:00:00'),
(1, 'Ngopi Santai', 'Menghabiskan sore di kafe sambil menikmati kopi. Waktu me-time yang sangat menyenangkan.', 'Grateful', '2025-01-13 17:45:00'),
(1, 'Hari yang Kurang Baik', 'Ada beberapa hal yang tidak berjalan sesuai rencana. Tapi aku mencoba tetap positif.', 'Sad', '2025-01-14 22:10:00');

-- 10. Verifikasi data
SELECT 'Users' as TableName, COUNT(*) as RecordCount FROM users
UNION ALL
SELECT 'Articles', COUNT(*) FROM articles
UNION ALL
SELECT 'Diary', COUNT(*) FROM diary
UNION ALL
SELECT 'Gallery', COUNT(*) FROM gallery;

-- Selesai! Database siap digunakan.
