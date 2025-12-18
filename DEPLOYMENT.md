# Railway MySQL Setup untuk Daily Journal

## Langkah Setup Railway:

### 1. Buat MySQL Database di Railway
- Login ke https://railway.app
- Klik "New Project" → "Provision MySQL"
- Tunggu hingga MySQL selesai dibuat

### 2. Dapatkan Kredensial MySQL
Buka tab "Variables" di Railway MySQL Anda, copy nilai berikut:
- `MYSQLHOST` (contoh: junction.proxy.rlwy.net)
- `MYSQLPORT` (contoh: 12345)
- `MYSQLUSER` (contoh: root)
- `MYSQLPASSWORD` (password yang digenerate Railway)
- `MYSQLDATABASE` (contoh: railway)

### 3. Setup Database Structure
Jalankan query SQL ini di Railway MySQL (bisa via Railway Query tab atau MySQL client):

```sql
CREATE DATABASE IF NOT EXISTS railway;
USE railway;

-- Tabel users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel articles
CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel diary
CREATE TABLE diary (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    mood VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel gallery
CREATE TABLE gallery (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (username, password, full_name) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'Administrator');
```

### 4. Deploy ke Vercel dengan Environment Variables

Di Vercel Project Settings → Environment Variables, tambahkan:
```
MYSQLHOST=junction.proxy.rlwy.net
MYSQLPORT=12345
MYSQLUSER=root
MYSQLPASSWORD=your-railway-password
MYSQLDATABASE=railway
```

### 5. Update dan Push ke GitHub
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/pbw
git add .
git commit -m "Add Vercel config and Railway MySQL support"
git push origin main
```

### 6. Redeploy di Vercel
- Vercel akan otomatis redeploy setelah push
- Atau manual trigger deploy di Vercel dashboard

## Troubleshooting:

### Jika Vercel masih download file:
1. Pastikan `vercel.json` sudah ada di root project
2. Vercel mungkin tidak support PHP dengan baik, gunakan alternatif:
   - **Railway** (bisa deploy PHP)
   - **Heroku** (dengan buildpack PHP)
   - **000webhost** atau **InfinityFree** (free PHP hosting)

### Untuk deploy di Railway (Full App + MySQL):
1. Buat New Project → Deploy from GitHub
2. Connect repository: AlvinMoved13/pbw
3. Add service → MySQL
4. Environment variables akan otomatis terset
5. Tambahkan file `railway.toml`:
```toml
[build]
builder = "nixpacks"

[deploy]
startCommand = "php -S 0.0.0.0:$PORT -t ."
```

## Catatan:
- File `koneksi.php` sudah diupdate untuk deteksi otomatis environment
- Local tetap menggunakan XAMPP MySQL
- Production menggunakan Railway MySQL via environment variables
