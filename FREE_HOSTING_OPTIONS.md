# 🆓 Pilihan Hosting Gratis untuk PHP + MySQL

## ⚠️ Update: Render & Vercel Tidak Support

- ❌ **Vercel:** Tidak support PHP (deprecated)
- ❌ **Render:** Free tier dihapus, minimal $7/bulan

## ✅ Alternatif Hosting Gratis

---

## 1. 🥇 InfinityFree (RECOMMENDED)

**Paling Mudah & Populer**

### Fitur:
- ✅ PHP 8.x support
- ✅ MySQL database unlimited
- ✅ 5GB disk space
- ✅ Unlimited bandwidth
- ✅ Free subdomain (*.epizy.com)
- ✅ cPanel control panel
- ✅ No ads (benar-benar gratis)
- ⚠️ Limit: 50,000 hits/day

### Cara Deploy:

1. **Daftar:**
   - Kunjungi [infinityfree.net](https://www.infinityfree.net)
   - Klik **Sign Up**
   - Isi form registrasi

2. **Buat Website:**
   - Dashboard → **Create Account**
   - Pilih subdomain gratis (contoh: `pbw.epizy.com`)
   - Tunggu aktivasi (~5 menit)

3. **Upload Files via cPanel:**
   - Login ke cPanel
   - File Manager → `/htdocs/`
   - Upload semua file PHP (kecuali `.git`, `node_modules`)
   - Struktur: `/htdocs/index.php`, `/htdocs/login.php`, dll

4. **Setup Database:**
   - cPanel → **MySQL Databases**
   - Create Database: `pbw_db`
   - Create User: `pbw_user`
   - Add User to Database
   - Klik **phpMyAdmin**
   - Import file `railway_setup.sql`

5. **Update koneksi.php:**
   ```php
   $servername = "sqlxxx.epizy.com"; // dari cPanel MySQL
   $username = "epiz_xxxxx_pbw";     // dari cPanel
   $password = "your_password";       // password database
   $db = "epiz_xxxxx_pbw";           // database name
   ```

6. **Akses:** `https://pbw.epizy.com`

---

## 2. 🥈 000webhost

**Mudah dengan Ads**

### Fitur:
- ✅ PHP 8.x
- ✅ MySQL 5.7
- ✅ 300MB storage
- ✅ 3GB bandwidth
- ✅ Free subdomain
- ⚠️ Ada banner iklan kecil
- ⚠️ Auto suspend jika tidak ada traffic 30 hari

### Cara Deploy:

1. **Daftar:** [000webhost.com](https://www.000webhost.com)
2. **Create Website** → Pilih subdomain
3. **File Manager** → Upload files ke `/public_html/`
4. **MySQL Management:**
   - Create database
   - Import `railway_setup.sql` via phpMyAdmin
5. **Update `koneksi.php`** dengan kredensial dari dashboard
6. **Akses:** `https://pbw.000webhostapp.com`

---

## 3. 🥉 Railway (Terbatas Gratis)

**Paling Modern, Free Tier Terbatas**

### Fitur:
- ✅ PHP + MySQL support
- ✅ Git-based deployment
- ✅ $5 kredit gratis per bulan
- ✅ 500 jam execution/bulan
- ⚠️ Kalau habis kredit harus bayar
- ⚠️ Auto sleep tidak ada, selalu running (habis kredit cepat)

### Cara Deploy:

Lihat panduan lengkap di [RAILWAY_FIX.md](RAILWAY_FIX.md)

**Estimasi penggunaan:**
- Database MySQL: ~$1.5/bulan
- Web service: ~$3/bulan
- Total: ~$4.5/bulan (masih dalam $5 gratis)

**Catatan:** Jika traffic tinggi, bisa habis kredit sebelum akhir bulan!

---

## 4. 🌐 Awardspace

**Gratis Tanpa Iklan**

### Fitur:
- ✅ PHP 8.x
- ✅ MySQL 5.7
- ✅ 1GB storage
- ✅ 5GB bandwidth
- ✅ No ads
- ⚠️ Agak lambat

### Deploy:
- Website: [awardspace.com](https://www.awardspace.com)
- Cara mirip dengan InfinityFree (cPanel)

---

## 5. 🛠️ Byethost

**Unlimited Space**

### Fitur:
- ✅ PHP 7.4+
- ✅ MySQL unlimited
- ✅ Unlimited storage & bandwidth
- ⚠️ Ada banner iklan
- ⚠️ Server kadang down

### Deploy:
- Website: [byethost.com](https://byet.host)
- Upload via FTP/cPanel
- Setup database via cPanel

---

## 📊 Perbandingan

| Hosting | PHP | MySQL | Storage | Bandwidth | Ads | Best For |
|---------|-----|-------|---------|-----------|-----|----------|
| **InfinityFree** | ✅ 8.x | ✅ Unlimited | 5GB | Unlimited | ❌ No | **Best Overall** |
| **000webhost** | ✅ 8.x | ✅ 5.7 | 300MB | 3GB | ⚠️ Yes | Small projects |
| **Railway** | ✅ Latest | ✅ Latest | 1GB | - | ❌ No | Modern stack |
| **Awardspace** | ✅ 8.x | ✅ 5.7 | 1GB | 5GB | ❌ No | No ads needed |
| **Byethost** | ✅ 7.4+ | ✅ Unlimited | Unlimited | Unlimited | ⚠️ Yes | Testing only |

---

## 🎯 Rekomendasi Berdasarkan Kebutuhan

### 📚 Untuk Tugas Kuliah / Portfolio:
**→ InfinityFree** (paling reliable dan tanpa iklan)

### 🚀 Untuk Production / Project Serius:
**→ Railway** (lebih profesional, Git-based)

### 💰 Budget Minim, Tidak Masalah Ada Iklan:
**→ 000webhost** (termudah)

### 🏢 Untuk Client / Profesional:
**→ Beli hosting berbayar:**
- Niagahoster: Rp 10,000/bulan
- Hostinger: Rp 15,000/bulan
- Dewaweb: Rp 20,000/bulan

---

## 🔧 File yang Perlu Diupdate untuk Shared Hosting

Untuk InfinityFree, 000webhost, Awardspace, Byethost:

### koneksi.php
```php
<?php
// Untuk shared hosting (InfinityFree, 000webhost, dll)
$is_local = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1');

if ($is_local) {
    // Local XAMPP
    $servername = "localhost";
    $username = "root";
    $password = "";
    $db = "webdailyjournal";
    $port = 3306;
} else {
    // Shared Hosting - GANTI DENGAN DATA DARI CPANEL!
    $servername = "sqlxxx.epizy.com";  // dari cPanel MySQL
    $username = "epiz_xxxxx_pbw";       // dari cPanel
    $password = "your_db_password";     // password database
    $db = "epiz_xxxxx_pbw";            // nama database
    $port = 3306;
}

$conn = new mysqli($servername, $username, $password, $db, $port);

if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Please check server configuration.");
}
?>
```

---

## ✅ Kesimpulan

**Untuk tugas kuliah Anda, saya rekomendasikan:**

1. **InfinityFree** - Paling mudah, tanpa iklan, reliable
2. **Railway** - Jika ingin pengalaman modern (tapi hati-hati kredit habis)
3. **000webhost** - Alternatif cepat (ada iklan kecil)

**Saya akan buatkan panduan detail untuk InfinityFree!** 🚀
