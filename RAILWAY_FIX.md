# PANDUAN DEPLOY RAILWAY - STEP BY STEP

## ⚠️ PENTING: Railway Connection Timeout Fix

Masalah "Connection timed out" terjadi karena **service pbw dan MySQL tidak terhubung**.

### Langkah 1: Link MySQL ke Service pbw

Di Railway Dashboard:
1. Klik service **pbw** (PHP app Anda)
2. Masuk ke tab **Settings**
3. Scroll ke bagian **Service**
4. Klik **+ Add Service Reference**
5. Pilih **MySQL** dari dropdown
6. Klik **Add**

Ini akan otomatis menambahkan environment variables ke service pbw:
- `MYSQLHOST`
- `MYSQLPORT`
- `MYSQLUSER`
- `MYSQLPASSWORD`
- `MYSQLDATABASE`

### Langkah 2: Setup Database Structure

Di Railway Dashboard, buka MySQL:
1. Klik service **MySQL**
2. Klik tab **Data**
3. Klik **Connect** (akan membuka Railway Query Editor)
4. Copy isi file `railway_setup.sql`
5. Paste ke Query Editor
6. Klik **Run Query**

Atau via CLI:
```bash
# Install Railway CLI
npm install -g @railway/cli

# Login
railway login

# Link project
railway link

# Run SQL script
railway run mysql -u root -p$(railway vars get MYSQLPASSWORD) railway < railway_setup.sql
```

### Langkah 3: Redeploy Service pbw

1. Klik service **pbw**
2. Klik tab **Deployments**
3. Klik **Redeploy** pada deployment terakhir

Atau push code baru:
```bash
git add .
git commit -m "Fix Railway MySQL connection"
git push origin main
```

### Langkah 4: Verify Connection

Setelah redeploy selesai:
1. Buka URL Railway Anda (klik **Settings** → copy **Domain**)
2. Halaman seharusnya loading tanpa error
3. Test login dengan:
   - Username: `admin`
   - Password: `admin123`

---

## 🔧 Troubleshooting

### Error: "Connection timed out"
**Penyebab:** Service pbw tidak bisa reach MySQL
**Solusi:** 
1. Pastikan MySQL dan pbw ada di **project yang sama**
2. Lakukan **Add Service Reference** (Langkah 1 di atas)
3. Redeploy service pbw

### Error: "Table doesn't exist"
**Penyebab:** Database belum di-setup
**Solusi:** Jalankan `railway_setup.sql` (Langkah 2 di atas)

### Error: "Access denied for user"
**Penyebab:** Kredensial MySQL salah
**Solusi:** 
1. Buka MySQL service → tab **Variables**
2. Verifikasi nilai MYSQLUSER dan MYSQLPASSWORD
3. Pastikan environment variables ter-inject ke service pbw

---

## 📊 Cek Environment Variables

Di service pbw, tab **Variables**, pastikan ada:
```
MYSQLHOST=junction.proxy.rlwy.net
MYSQLPORT=xxxx
MYSQLUSER=root
MYSQLPASSWORD=xxxxxxxxxx
MYSQLDATABASE=railway
```

Jika tidak ada, lakukan **Add Service Reference** lagi.

---

## 🚀 Deploy Ulang

Setelah fix:
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/pbw
git add .
git commit -m "Fix Railway MySQL connection and add setup script"
git push origin main
```

Railway akan otomatis redeploy.

---

## ✅ Expected Result

Setelah semua langkah selesai:
- ✅ Service pbw bisa connect ke MySQL
- ✅ Database memiliki 4 tabel (users, articles, diary, gallery)
- ✅ Ada 1 user admin dan sample data
- ✅ Website bisa diakses dan login berhasil
- ✅ Healthcheck PASS
