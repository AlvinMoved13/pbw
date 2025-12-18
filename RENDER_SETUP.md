# 🚀 PANDUAN DEPLOY KE RENDER

## Kenapa Render?

✅ **Support PHP & MySQL** (tidak seperti Vercel)  
✅ **Free tier tersedia** dengan MySQL  
✅ **Mudah setup** dari GitHub  
✅ **Auto deploy** setiap push ke GitHub  

---

## 📋 Langkah-Langkah Deploy (100% GRATIS)

### 1. Persiapan GitHub

Pastikan semua file sudah di-push ke GitHub:
```bash
git add .
git commit -m "Deploy to Render"
git push origin main
```

### 2. Buat Akun Render

1. Kunjungi [render.com](https://render.com)
2. Klik **Get Started for Free**
3. Sign up dengan GitHub account
4. Authorize Render untuk akses repository

---

## 🗄️ STEP 1: Deploy MySQL Database (Private Service)

1. Di Render Dashboard, klik **New +**
2. Pilih **Private Service** (JANGAN pilih Web Service atau Static Site!)
3. Klik **Build and deploy from a Git repository** → **Next**
4. Connect repository: **AlvinMoved13/pbw** → **Connect**

5. **Isi Form MySQL:**
   - **Name:** `pbw-mysql`
   - **Region:** Singapore (atau terdekat)
   - **Branch:** `main`
   - **Root Directory:** kosongkan
   - **Environment:** **Docker**
   - **Dockerfile Path:** `./Dockerfile.mysql`
   - **Instance Type:** **Free**

6. **Environment Variables** - Klik **Add Environment Variable**:
   ```
   Key: MYSQL_ROOT_PASSWORD
   Value: pbw_password_2024 (atau password kuat lainnya)
   
   Key: MYSQL_DATABASE
   Value: railway
   ```

7. Klik **Create Private Service**

8. **TUNGGU** sampai status **Live** (~3-5 menit)

9. **CATAT** informasi penting:
   - Klik service **pbw-mysql**
   - Lihat **Internal Hostname** (contoh: `pbw-mysql-xxxx`)
   - SIMPAN hostname ini, akan dipakai di Step 2

---

## 🌐 STEP 2: Deploy PHP Application (Web Service)

1. Dashboard → **New +**
2. Pilih **Web Service** (bukan Private Service atau Static Site!)
3. Klik **Build and deploy from a Git repository** → **Next**
4. Connect repository: **AlvinMoved13/pbw** → **Connect**

5. **Isi Form PHP:**
   - **Name:** `pbw`
   - **Region:** Singapore (HARUS sama dengan MySQL!)
   - **Branch:** `main`
   - **Root Directory:** kosongkan
   - **Environment:** **Docker**
   - **Dockerfile Path:** `./Dockerfile`
   - **Instance Type:** **Free**

6. **Environment Variables** - Klik **Add Environment Variable**:
   ```
   Key: MYSQLHOST
   Value: pbw-mysql-xxxx (paste Internal Hostname dari Step 1)
   
   Key: MYSQLPORT
   Value: 3306
   
   Key: MYSQLUSER
   Value: root
   
   Key: MYSQLPASSWORD
   Value: pbw_password_2024 (SAMA dengan Step 1)
   
   Key: MYSQLDATABASE
   Value: railway
   ```

7. Klik **Create Web Service**

8. **TUNGGU** sampai status **Live** (~5-7 menit)

---

## ✅ STEP 3: Akses Aplikasi

1. Klik service **pbw** di dashboard
2. Copy URL yang muncul (format: `https://pbw-xxxx.onrender.com`)
3. Buka di browser
4. **Login:**
   - Username: `admin`
   - Password: `admin123`

**🎉 SELESAI! Aplikasi sudah online!**

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
