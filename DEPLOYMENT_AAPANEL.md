# Panduan Deployment ke VPS menggunakan aaPanel

Dokumen ini adalah panduan lengkap untuk men-deploy aplikasi **Komiktap** (Laravel 12) ke VPS menggunakan **aaPanel** control panel.

---

## 📋 Daftar Isi

1. [Persyaratan](#1-persyaratan)
2. [Instalasi aaPanel di VPS](#2-instalasi-aapanel-di-vps)
3. [Konfigurasi Environment di aaPanel](#3-konfigurasi-environment-di-aapanel)
4. [Upload & Setup Aplikasi](#4-upload--setup-aplikasi)
5. [Konfigurasi Database](#5-konfigurasi-database)
6. [Setup Website di aaPanel](#6-setup-website-di-aapanel)
7. [SSL Certificate](#7-ssl-certificate)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. Persyaratan

### VPS Requirements:
- **OS**: Ubuntu 20.04/22.04 atau Debian 10/11
- **RAM**: Minimal 1GB (2GB+ direkomendasikan)
- **Storage**: Minimal 10GB
- **IP Public**: Untuk akses domain
- **Root Access**: SSH dengan user root atau sudo

### Lokal Requirements:
- Git (untuk clone/pull update)
- SSH Client (Terminal/PuTTY)
- FTP Client (FileZilla/Cyberduck) - opsional

---

## 2. Instalasi aaPanel di VPS

### Step 1: Koneksi ke VPS via SSH

```bash
ssh root@IP_VPS_ANDA
# Masukkan password saat diminta
```

### Step 2: Update System

```bash
apt update && apt upgrade -y
```

### Step 3: Install aaPanel

Jalankan script instalasi aaPanel:

```bash
wget -O install.sh http://www.aapanel.com/script/install-ubuntu_6.0_en.sh && bash install.sh aapanel
```

> **Catatan**: 
> - Untuk Debian, gunakan: `install_6.0_en.sh`
> - Untuk CentOS, ganti dengan script CentOS

**Proses instalasi memakan waktu 5-15 menit**. Setelah selesai, Anda akan mendapatkan:
```
==================================================================
Congratulations! Installed successfully!
==================================================================
aaPanel URL: http://IP_VPS_ANDA:7800/xxxxxxxx
Username: xxxxxxxx
Password: xxxxxxxx
==================================================================
```

**PENTING**: Simpan URL, username, dan password ini!

### Step 4: Login ke aaPanel

1. Buka browser dan akses URL yang diberikan
2. Login dengan username & password
3. Pilih environment LNMP (Linux + Nginx + MySQL + PHP)

---

## 3. Konfigurasi Environment di aaPanel

### Step 1: Install Software Stack

Setelah login pertama kali, aaPanel akan menampilkan dialog untuk install software. Pilih:

#### Recommended Stack (LNMP):
- **Nginx**: Versi Stable terbaru
- **MySQL**: 8.0+ atau **PostgreSQL 14+** (pilih sesuai kebutuhan)
- **PHP**: **8.2** (WAJIB untuk Laravel 12)
- **phpMyAdmin**: Latest (opsional, untuk MySQL)
- **Redis**: Latest (opsional, untuk cache/queue)

Klik "One Click Install" dan tunggu hingga selesai (15-30 menit).

### Step 2: Konfigurasi PHP 8.2

Setelah instalasi selesai:

1. Di sidebar, klik **App Store**
2. Cari **PHP 8.2**
3. Klik **Settings** (ikon roda gigi)
4. Tab **Install Extensions**, install ekstensi berikut:

**Wajib Install**:
- `opcache` ✅
- `redis` (jika pakai Redis)
- `fileinfo` ✅
- `imagemagick` (untuk image processing)
- `pgsql` & `pdo_pgsql` (jika pakai PostgreSQL/Supabase) ✅✅

5. Tab **Configuration File**, cari dan pastikan setting ini:

```ini
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
memory_limit = 256M
```

6. Klik **Save** dan **Restart PHP-FPM**

### Step 3: Install Composer

Di terminal aaPanel atau SSH:

```bash
# Install Composer globally
cd ~
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Verifikasi
composer --version
```

### Step 4: Install Node.js & NPM

```bash
# Install NVM (Node Version Manager)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc

# Install Node.js 18+
nvm install 18
nvm use 18
node --version
npm --version
```

---

## 4. Upload & Setup Aplikasi

### Metode 1: Git Clone (Recommended)

#### Step 1: Generate SSH Key di VPS (Untuk Private Repo)

```bash
ssh-keygen -t ed25519 -C "your_email@example.com"
# Tekan Enter untuk default location
cat ~/.ssh/id_ed25519.pub
```

Copy public key dan tambahkan ke GitHub/GitLab Settings → SSH Keys.

#### Step 2: Clone Repository

```bash
cd /www/wwwroot
git clone git@github.com:shirokun20/komiktap-web.git komiktap.com
cd komiktap.com
```

### Metode 2: Upload Manual via SFTP

1. Buka FileZilla/Cyberduck
2. Koneksi ke VPS:
   - Host: IP VPS
   - Protocol: SFTP
   - Port: 22 (default SSH)
   - User: root
   - Password: password VPS
3. Upload folder proyek ke `/www/wwwroot/komiktap.com`

### Step 3: Set Permission

```bash
cd /www/wwwroot/komiktap.com

# Set ownership
chown -R www:www /www/wwwroot/komiktap.com

# Set permission
chmod -R 755 /www/wwwroot/komiktap.com
chmod -R 775 storage bootstrap/cache
```

### Step 4: Install Dependencies

```bash
# Install Composer Dependencies
composer install --no-dev --optimize-autoloader

# Install NPM Dependencies
npm install

# Build Assets Production
npm run build
```

> **Catatan**: Jika ingin menggunakan `composer setup` script, pastikan `.env` sudah dikonfigurasi terlebih dahulu.

### Step 5: Setup Environment

```bash
# Copy .env.example
cp .env.example .env

# Generate App Key
php artisan key:generate

# Edit .env
nano .env
```

**Konfigurasi `.env` penting**:

```env
APP_NAME="Komiktap"
APP_ENV=production
APP_DEBUG=false  # WAJIB false di production!
APP_URL=https://komiktap.com

# Database (sesuaikan dengan database yang dibuat)
DB_CONNECTION=mysql  # atau pgsql untuk PostgreSQL
DB_HOST=127.0.0.1
DB_PORT=3306         # 5432 untuk PostgreSQL
DB_DATABASE=komiktap_db
DB_USERNAME=komiktap_user
DB_PASSWORD=password_kuat_anda

# Queue & Cache (opsional)
QUEUE_CONNECTION=database  # atau redis jika sudah setup
CACHE_DRIVER=file          # atau redis

# Mail (sesuaikan dengan SMTP Anda)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@komiktap.com
MAIL_FROM_NAME="${APP_NAME}"

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Filament
FILAMENT_DOMAIN=null  # Atau subdomain jika ingin pisah admin
```

Simpan dengan `Ctrl+O`, Enter, `Ctrl+X`.

---

## 5. Konfigurasi Database

### Opsi A: MySQL (via aaPanel)

1. Di sidebar aaPanel, klik **Database**
2. Klik **Add Database**
3. Isi:
   - **Database Name**: `komiktap_db`
   - **Username**: `komiktap_user`
   - **Password**: `password_kuat_anda`
4. Klik **Submit**

### Opsi B: PostgreSQL (via aaPanel)

1. Install PostgreSQL dari **App Store** jika belum
2. Di sidebar, klik **Database** → **PostgreSQL**
3. Buat database via terminal:

```bash
# Masuk ke PostgreSQL
su - postgres
psql

-- Buat database dan user
CREATE DATABASE komiktap_db;
CREATE USER komiktap_user WITH PASSWORD 'password_kuat_anda';
GRANT ALL PRIVILEGES ON DATABASE komiktap_db TO komiktap_user;
\q
exit
```

### Opsi C: Supabase (Cloud PostgreSQL)

Jika menggunakan Supabase, skip pembuatan database lokal. Edit `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
DB_PORT=6543  # Transaction mode
DB_DATABASE=postgres
DB_USERNAME=postgres.xxxxx
DB_PASSWORD=your_supabase_password
DB_SSLMODE=prefer
```

Tambahkan di `config/database.php` bagian `pgsql`:

```php
'pgsql' => [
    // ... existing config
    'options' => [
        PDO::ATTR_EMULATE_PREPARES => true,
    ],
],
```

### Step: Jalankan Migrasi

```bash
cd /www/wwwroot/komiktap.com
php artisan migrate --force
```

Jika ada seeder untuk admin user:

```bash
php artisan db:seed --class=AdminSeeder
```

---

## 6. Setup Website di aaPanel

### Step 1: Tambah Website Baru

1. Di sidebar aaPanel, klik **Website**
2. Klik **Add Site**
3. Isi form:
   - **Domain**: `komiktap.com` (atau subdomain seperti `app.komiktap.com`)
   - **Additional Domains**: `www.komiktap.com` (opsional)
   - **Root Directory**: `/www/wwwroot/komiktap.com/public` ⚠️ **PENTING: harus ke folder /public**
   - **PHP Version**: **PHP-82**
   - **Create Database**: Skip (sudah dibuat manual)
4. Klik **Submit**

### Step 2: Konfigurasi Nginx (untuk subdomain API - opsional)

Jika ingin pisahkan API dengan subdomain `api.komiktap.com`:

1. Klik **Website** → pilih site `komiktap.com` → **Settings**
2. Tab **Config File**, tambahkan server block baru atau buat site baru untuk `api.komiktap.com` dengan root yang SAMA `/www/wwwroot/komiktap.com/public`

### Step 3: Test Akses

Buka browser dan akses domain Anda:
- `http://komiktap.com` → Harus menampilkan aplikasi
- `http://komiktap.com/admin` → Halaman login Filament

Jika muncul **500 Error**, cek log:

```bash
tail -f /www/wwwroot/komiktap.com/storage/logs/laravel.log
```

---

## 7. SSL Certificate

### Step 1: Install Let's Encrypt SSL

1. Di **Website** list, klik site Anda
2. Klik **Settings** → Tab **SSL**
3. Pilih **Let's Encrypt**
4. Centang domain yang ingin ditambahkan SSL:
   - ✅ `komiktap.com`
   - ✅ `www.komiktap.com`
   - ✅ `api.komiktap.com` (jika ada)
5. Isi email untuk notifikasi
6. Klik **Apply**

Certificate akan otomatis dipasang dan di-renew setiap 90 hari.

### Step 2: Force HTTPS

1. Tab **SSL** → centang **Force HTTPS**
2. Update `.env`:

```env
APP_URL=https://komiktap.com
```

3. Clear cache:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 8. Troubleshooting

### Problem 1: "500 Internal Server Error"

**Solusi**:
```bash
# Cek permission
chmod -R 775 storage bootstrap/cache

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Cek log
tail -f storage/logs/laravel.log
```

### Problem 2: "Composer install gagal (memory limit)"

**Solusi**:
```bash
# Increase PHP memory limit sementara
php -d memory_limit=-1 /usr/local/bin/composer install --no-dev
```

### Problem 3: "Page tidak load CSS/JS"

**Solusi**:
```bash
# Build ulang assets
npm run build

# Publish assets Laravel/Filament
php artisan vendor:publish --tag=laravel-assets --force
php artisan filament:upgrade

# Clear cache
php artisan optimize
```

### Problem 4: "Database Connection Error (PostgreSQL)"

**Solusi**:
- Pastikan ekstensi `pgsql` dan `pdo_pgsql` sudah ter-install di PHP 8.2
- Restart PHP-FPM di aaPanel
- Cek `config/database.php` apakah ada typo

```bash
# Test koneksi PostgreSQL
php artisan tinker
>>> DB::connection()->getPdo();
```

### Problem 5: "Permission Denied saat Upload File"

**Solusi**:
```bash
# Set ownership ke web server user
chown -R www:www /www/wwwroot/komiktap.com

# Set permission storage
chmod -R 775 storage
```

---

## 9. Post-Deployment Checklist

✅ `.env` sudah set `APP_DEBUG=false`  
✅ `.env` sudah set `APP_ENV=production`  
✅ SSL Certificate terinstall dan force HTTPS aktif  
✅ Database migration berjalan tanpa error  
✅ Admin panel bisa diakses di `/admin`  
✅ Storage permission sudah benar (`775`)  
✅ Cache sudah di-clear (`config:clear`, `cache:clear`, `optimize`)  
✅ Assets production sudah di-build (`npm run build`)  
✅ Cron jobs untuk queue/scheduler sudah setup (jika perlu)

---

## 10. Update Aplikasi (Git Pull)

Untuk update aplikasi di masa depan:

```bash
cd /www/wwwroot/komiktap.com

# Backup database terlebih dahulu!
# mysqldump -u root -p komiktap_db > backup_$(date +%Y%m%d).sql

# Pull update
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Jalankan migration (jika ada)
php artisan migrate --force

# Clear cache
php artisan optimize
php artisan view:clear

# Set permission lagi (untuk file baru)
chown -R www:www .
chmod -R 775 storage bootstrap/cache
```

---

## 🎉 Selesai!

Aplikasi Komiktap Anda sekarang sudah live di VPS dengan aaPanel. 

**Useful Commands**:
- Restart Nginx: Di aaPanel → App Store → Nginx → Restart
- Restart PHP-FPM: Di aaPanel → App Store → PHP 8.2 → Reload Configuration
- Cek Log Nginx: `/www/server/panel/logs/error.log`
- Cek Log Laravel: `tail -f storage/logs/laravel.log`

**Support**:
- aaPanel Docs: https://www.aapanel.com/reference.html
- Laravel Docs: https://laravel.com/docs/12.x
