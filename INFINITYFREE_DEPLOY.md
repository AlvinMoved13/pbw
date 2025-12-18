# 🚀 Deploy ke InfinityFree - Step by Step

## Kenapa InfinityFree?

✅ **100% Gratis Selamanya**  
✅ **Tanpa Iklan**  
✅ **PHP 8.x + MySQL**  
✅ **cPanel Lengkap**  
✅ **50,000 hits/day** (cukup untuk project kuliah)  

---

## 📋 Langkah 1: Daftar Akun

1. Buka [infinityfree.net](https://www.infinityfree.net)
2. Klik **Sign Up** (pojok kanan atas)
3. Isi form:
   - **Email:** Email aktif Anda
   - **Password:** Password kuat
4. Verifikasi email (check inbox/spam)
5. Login ke dashboard

---

## 🌐 Langkah 2: Buat Website

1. Di dashboard, klik **Create Account**
2. Isi form:
   - **Domain Type:** Use free subdomain
   - **Subdomain:** `pbw` (atau nama lain)
   - **Domain:** Pilih `.rf.gd` atau `.epizy.com`
   - **Password:** Password untuk cPanel (berbeda dengan login)
3. Klik **Create Account**
4. **Tunggu 5-10 menit** sampai status **Active**

---

## 📁 Langkah 3: Upload Files

### Via cPanel File Manager (Recommended):

1. Dashboard → Klik **Control Panel** (cPanel icon)
2. Login dengan password yang dibuat di Langkah 2
3. Klik **Online File Manager**
4. Masuk ke folder **htdocs**
5. **Delete** file `default.php` dan `index.html` bawaan
6. Klik **Upload** (pojok kanan atas)

### File yang di-upload:

Upload semua file KECUALI:
- ❌ `.git/` folder
- ❌ `node_modules/` folder
- ❌ `Dockerfile*`
- ❌ `render.yaml`
- ❌ `railway.toml`
- ❌ `*.md` files (README, DEPLOYMENT, dll)

✅ Upload file ini:
- `index.php`
- `login.php`
- `dashboard.php`
- `logout.php`
- `koneksi.php`
- `article.php`, `diary.php`, `gallery.php`
- `style.css`
- `uploads/` folder (buat manual jika belum ada)
- `railway_setup.sql` (untuk import database)

### Via FTP (Alternatif):

1. Download FileZilla Client
2. Connect dengan:
   - **Host:** `ftpupload.net`
   - **Username:** `epiz_xxxxx` (dari cPanel)
   - **Password:** Password cPanel
   - **Port:** 21
3. Upload ke folder `/htdocs/`

---

## 🗄️ Langkah 4: Setup Database MySQL

1. Di cPanel, klik **MySQL Databases**
2. **Create New Database:**
   - Database Name: `pbw` (akan jadi: `epiz_xxxxx_pbw`)
   - Klik **Create Database**
3. **Create User:**
   - Username: `pbwuser`
   - Password: Generate atau buat sendiri (CATAT!)
   - Klik **Create User**
4. **Add User to Database:**
   - User: Pilih `pbwuser`
   - Database: Pilih `pbw`
   - Klik **Add**
   - Pilih **ALL PRIVILEGES**
   - Klik **Make Changes**

---

## 📊 Langkah 5: Import Database Structure

1. Di cPanel, klik **phpMyAdmin**
2. Login otomatis
3. Di sidebar kiri, klik database `epiz_xxxxx_pbw`
4. Klik tab **SQL** (atas)
5. Buka file `railway_setup.sql` di VS Code
6. **Copy semua isi file**
7. **Paste** ke SQL query box di phpMyAdmin
8. Klik **Go** (kanan bawah)
9. Tunggu success message
10. Klik tab **Structure** - harus ada 4 tables:
    - `users`
    - `articles`
    - `diary`
    - `gallery`

---

## ⚙️ Langkah 6: Update koneksi.php

1. Di File Manager, buka file **koneksi.php**
2. Klik **Edit** (icon pensil)
3. **Replace semua isi** dengan kode ini:

```php
<?php
// Database connection dengan auto-detection
$is_local = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1');

if ($is_local) {
    // Local XAMPP
    $servername = "localhost";
    $username = "root";
    $password = "";
    $db = "webdailyjournal";
} else {
    // InfinityFree Hosting
    // GANTI dengan data dari cPanel MySQL Databases!
    $servername = "sqlXXX.epizy.com";      // MySQL Hostname dari cPanel
    $username = "epiz_xxxxx_pbwuser";      // MySQL Username dari cPanel
    $password = "your_database_password";  // Password yang dibuat di Langkah 4
    $db = "epiz_xxxxx_pbw";               // Database Name dari cPanel
}

$conn = new mysqli($servername, $username, $password, $db);

if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Gagal koneksi database. Hubungi administrator.");
}
?>
```

4. **PENTING:** Ganti nilai berikut dengan data dari cPanel:
   - `sqlXXX.epizy.com` → MySQL Hostname (ada di cPanel MySQL Databases)
   - `epiz_xxxxx_pbwuser` → MySQL Username
   - `your_database_password` → Password yang dibuat
   - `epiz_xxxxx_pbw` → Database Name

5. **Save Changes**

---

## 🎯 Langkah 7: Set Permissions untuk Upload Folder

1. File Manager → Buka folder **uploads**
2. **Klik kanan** folder `uploads` → **Change Permissions**
3. Centang semua checkbox:
   - Owner: Read, Write, Execute
   - Group: Read, Write, Execute
   - World: Read, Write, Execute
4. Atau set **777** (numerical)
5. Klik **Change Permissions**

---

## ✅ Langkah 8: Test Aplikasi

1. Buka browser
2. Akses `https://pbw.rf.gd` (atau subdomain Anda)
3. Harus muncul halaman index
4. Klik **Login**
5. Login dengan:
   - **Username:** `admin`
   - **Password:** `admin123`
6. Harus masuk ke dashboard
7. **Test fitur:**
   - Create Article
   - Create Diary
   - Upload Gallery

---

## 🐛 Troubleshooting

### Error: "Connection failed"
**Penyebab:** Database credentials salah  
**Solusi:**
1. Cek kembali MySQL Hostname di cPanel
2. Pastikan Username dan Password benar
3. Pastikan database sudah di-import

### Error: "404 Not Found"
**Penyebab:** File tidak di-upload ke folder htdocs  
**Solusi:**
1. Pastikan semua file ada di `/htdocs/` (bukan subfolder)
2. Struktur: `/htdocs/index.php`, bukan `/htdocs/pbw/index.php`

### Error: "Failed to upload image"
**Penyebab:** Folder uploads tidak ada atau tidak writable  
**Solusi:**
1. Buat folder `uploads` di `/htdocs/`
2. Set permission 777 (Langkah 7)

### Error: "Too many requests"
**Penyebab:** Hit limit 50,000/day terlampaui  
**Solusi:**
1. Tunggu 24 jam
2. Atau upgrade ke premium

### Website Loading Lambat
**Normal:** InfinityFree kadang agak lambat di jam sibuk  
**Tips:**
- Optimize gambar sebelum upload
- Minimize CSS/JS
- Gunakan caching

---

## 📝 Informasi Database untuk Dicatat

Simpan informasi ini (ada di cPanel MySQL Databases):

```
MySQL Hostname: sqlXXX.epizy.com
MySQL Database: epiz_xxxxx_pbw
MySQL Username: epiz_xxxxx_pbwuser
MySQL Password: [password yang Anda buat]
MySQL Port: 3306
```

---

## 🔐 Login Credentials

**Website Login:**
- URL: `https://pbw.rf.gd/login.php`
- Username: `admin`
- Password: `admin123`

**cPanel Login:**
- URL: `https://cpanel.infinityfree.net`
- Username: `epiz_xxxxx`
- Password: [password cPanel]

**phpMyAdmin:**
- Via cPanel → phpMyAdmin icon
- Auto login

---

## 🎓 Tips untuk Tugas Kuliah

1. **Screenshot untuk dokumentasi:**
   - Dashboard InfinityFree
   - cPanel file manager
   - phpMyAdmin tables
   - Website tampilan index
   - Website tampilan dashboard
   - Test CRUD operations

2. **Buat akun user baru** (bukan admin) untuk testing

3. **Catat URL** untuk dikumpulkan ke dosen

4. **Test di mobile** juga (responsive)

---

## 🚀 Done!

Website Anda sekarang online di:
**https://pbw.rf.gd** (atau subdomain Anda)

Gratis selamanya, tanpa iklan! 🎉

**Butuh bantuan?** Lihat [FREE_HOSTING_OPTIONS.md](FREE_HOSTING_OPTIONS.md) untuk alternatif hosting lain.
