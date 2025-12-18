# 🚀 PANDUAN DEPLOY KE RENDER

## Kenapa Render?

✅ **Support PHP & MySQL** (tidak seperti Vercel)  
✅ **Free tier tersedia** dengan MySQL  
✅ **Mudah setup** dari GitHub  
✅ **Auto deploy** setiap push ke GitHub  

---

## 📋 Langkah-Langkah Deploy

### 1. Persiapan GitHub

Pastikan semua file sudah di-push ke GitHub:
```bash
git add .
git commit -m "Add Render configuration"
git push origin main
```

### 2. Buat Akun Render

1. Kunjungi [render.com](https://render.com)
2. Klik **Get Started for Free**
3. Sign up dengan GitHub account
4. Authorize Render untuk akses repository

### 3. Deploy MySQL Database

1. Di Render Dashboard, klik **New +** → **Blueprint**
2. Connect repository: **AlvinMoved13/pbw**
3. Klik **Apply**

Render akan otomatis:
- Buat MySQL service dari `Dockerfile.mysql`
- Buat PHP web service dari `Dockerfile`
- Link kedua services via environment variables
- Run `railway_setup.sql` untuk setup database

### 4. Tunggu Deploy Selesai

- MySQL service: ~3-5 menit
- PHP web service: ~5-7 menit
- Status: Dashboard → Services → **pbw** → Lihat logs

### 5. Akses Aplikasi

Setelah deploy selesai:
1. Klik service **pbw** di dashboard
2. Copy URL (format: `https://pbw-xxxx.onrender.com`)
3. Buka di browser

**Login credentials:**
- Username: `admin`
- Password: `admin123`

---

## 🔧 Alternatif: Deploy Manual (Tanpa Blueprint)

Jika render.yaml tidak otomatis terdeteksi:

### A. Deploy MySQL Dulu

1. Dashboard → **New +** → **Web Service**
2. Connect repository: **AlvinMoved13/pbw**
3. Isi form:
   - **Name:** `pbw-mysql`
   - **Environment:** `Docker`
   - **Dockerfile Path:** `./Dockerfile.mysql`
   - **Plan:** Free
4. Environment Variables:
   ```
   MYSQL_ROOT_PASSWORD=<generate random password>
   MYSQL_DATABASE=railway
   ```
5. Klik **Create Web Service**

### B. Deploy PHP Application

1. Dashboard → **New +** → **Web Service**
2. Connect repository: **AlvinMoved13/pbw**
3. Isi form:
   - **Name:** `pbw`
   - **Environment:** `Docker`
   - **Dockerfile Path:** `./Dockerfile`
   - **Plan:** Free
4. Environment Variables (ambil dari MySQL service):
   ```
   MYSQLHOST=<internal hostname dari pbw-mysql>
   MYSQLPORT=3306
   MYSQLUSER=root
   MYSQLPASSWORD=<password yang di-generate>
   MYSQLDATABASE=railway
   ```
5. Klik **Create Web Service**

---

## ⚠️ Catatan Penting

### Free Tier Limitations

- **750 jam per bulan** (cukup untuk 1 project 24/7)
- **Auto sleep** setelah 15 menit tidak ada traffic
- **Cold start** ~30 detik saat bangun dari sleep
- **Disk:** 1GB untuk database

### Menghindari Sleep

Gunakan cron job untuk ping setiap 10 menit:
```bash
# Contoh: UptimeRobot atau cron-job.org
curl https://pbw-xxxx.onrender.com
```

### Jika Deploy Gagal

1. **Check Logs:**
   - Service → **Logs** tab
   - Lihat error message

2. **Common Issues:**
   - **Database connection failed:** Pastikan MYSQLHOST benar
   - **Port already in use:** Restart service
   - **Dockerfile not found:** Check path di settings

3. **Rebuild:**
   - Service → **Manual Deploy** → **Clear build cache & deploy**

---

## 🔄 Update Aplikasi

Setiap kali push ke GitHub:
```bash
git add .
git commit -m "Update feature"
git push origin main
```

Render akan otomatis:
1. Detect perubahan di GitHub
2. Rebuild Docker image
3. Redeploy service
4. Zero downtime deployment

---

## 📊 Monitoring

### Check Status
- Dashboard → Service **pbw** → **Events** tab
- Lihat deploy history dan status

### View Logs
- Service → **Logs** tab
- Real-time logs dari PHP dan Apache

### Database Access
- Service **pbw-mysql** → **Shell** tab
- Connect via MySQL client:
  ```bash
  mysql -h <MYSQLHOST> -u root -p
  ```

---

## 🆚 Render vs Railway vs Vercel

| Feature | Render | Railway | Vercel |
|---------|--------|---------|--------|
| PHP Support | ✅ Yes | ✅ Yes | ❌ No |
| MySQL | ✅ Yes | ✅ Yes | ❌ No |
| Free Tier | ✅ Yes | ✅ Yes (limited) | ✅ Yes |
| Auto Sleep | ✅ 15 min | ❌ No | ✅ No |
| Setup | 🟢 Easy | 🟡 Medium | 🔴 Impossible |

**Rekomendasi:** Render untuk free tier, Railway untuk production.

---

## 🎯 Kesimpulan

Render adalah alternatif terbaik untuk:
- ✅ Deploy PHP + MySQL gratis
- ✅ Auto deploy dari GitHub
- ✅ Mudah setup
- ✅ Support Docker

**Next Steps:**
1. Push kode ke GitHub
2. Deploy via Render Blueprint
3. Test aplikasi
4. Setup uptime monitoring (opsional)

Selamat mencoba! 🚀
