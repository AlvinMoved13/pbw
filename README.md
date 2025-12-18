# My Daily Journal - Deployment Guide

## ⚠️ PERHATIAN: Deploy di Railway, BUKAN Vercel

**Vercel tidak support PHP**. Package `@vercel/php` sudah deprecated dan tidak tersedia lagi.

## ✅ SOLUSI: Gunakan Railway untuk Full Deployment

Railway support PHP native dan lebih mudah untuk project PHP seperti ini.

---

## 🚀 DEPLOY KE RAILWAY (REKOMENDASI)

### Setup Railway (Jika Belum)

1. **Login ke Railway**
   - Buka https://railway.app
   - Login dengan GitHub

2. **Create New Project**
   - Klik **New Project**
   - Pilih **Deploy from GitHub repo**
   - Pilih repository: `AlvinMoved13/pbw`
   - Railway akan otomatis detect PHP dan mulai build

3. **Add MySQL Service**
   - Di project yang sama, klik **+ New**
   - Pilih **Database → MySQL**
   - MySQL akan dibuat otomatis

4. **Link MySQL ke PHP Service** (PENTING!)
   - Klik service **pbw** (PHP app)
   - Tab **Settings**
   - Scroll ke **Service References**
   - Klik **+ New Variable → Add Service Variable**
   - Pilih **MySQL**
   - Ini auto-inject environment variables: MYSQLHOST, MYSQLPORT, dll

5. **Setup Database Structure**
   - Klik service **MySQL**
   - Tab **Data**
   - Klik **Connect** (buka Query Editor)
   - Copy paste isi file `railway_setup.sql`
   - Run Query
   - Ini akan create tables dan insert sample data

6. **Redeploy PHP Service**
   - Klik service **pbw**
   - Tab **Deployments**
   - Klik **⋮** → **Redeploy**

7. **Generate Public Domain**
   - Klik service **pbw**
   - Tab **Settings**
   - Scroll ke **Networking → Public Networking**
   - Klik **Generate Domain**
   - Copy URL (contoh: pbw-production-xxxx.up.railway.app)

8. **Test Website**
   - Buka URL Railway
   - Login dengan:
     - Username: `admin`
     - Password: `admin123`

---

## 📁 File Penting untuk Railway

- ✅ `railway.toml` - Konfigurasi deployment Railway
- ✅ `railway_setup.sql` - Script setup database
- ✅ `koneksi.php` - Auto-detect environment (local/production)
- ✅ `RAILWAY_FIX.md` - Troubleshooting guide

---

## 🔧 Troubleshooting

### Error: "Connection timed out"
**Solusi:** Pastikan sudah link MySQL ke service pbw (langkah 4 di atas)

### Error: "Table doesn't exist"
**Solusi:** Run `railway_setup.sql` di MySQL Query Editor (langkah 5 di atas)

### Error: "Build failed"
**Solusi:** Pastikan file `railway.toml` ada di root directory

---

## 🌐 Alternatif Hosting PHP Gratis

Jika Railway tidak cocok, alternatif lain:

1. **InfinityFree** (https://infinityfree.net)
   - Support PHP & MySQL gratis
   - Unlimited bandwidth
   - cPanel included

2. **000webhost** (https://www.000webhost.com)
   - 300 MB storage
   - PHP & MySQL support
   - No ads

3. **Heroku** (https://heroku.com)
   - Butuh credit card (tapi free tier tersedia)
   - Good untuk production

---

## 📝 Development Local

Untuk development di XAMPP local tetap berjalan normal:
```bash
# Start XAMPP
# Buka http://localhost/pbw/
```

File `koneksi.php` otomatis detect environment:
- Local → Pakai `localhost` MySQL
- Railway → Pakai Railway MySQL dengan environment variables

---

## 🎯 Kesimpulan

❌ **Jangan deploy ke Vercel** - PHP tidak support
✅ **Deploy ke Railway** - Full support PHP + MySQL, gratis, dan mudah

Pertanyaan? Check `RAILWAY_FIX.md` untuk detail troubleshooting!
