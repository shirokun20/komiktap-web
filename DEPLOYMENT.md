# Panduan Deployment & Instalasi Aplikasi Komiktap

Dokumen ini berisi panduan lengkap untuk menginstal dan men-deploy aplikasi **Komiktap** dari tahap development (lokal) hingga ke production (Hosting/VPS). Panduan ini mencakup penggunaan **PostgreSQL** dan **Supabase**.

---

## 1. Persyaratan Sistem (Server Requirements)

Sebelum memulai, pastikan server (VPS/Hosting) Anda memenuhi kriteria berikut:

*   **OS**: Linux (Ubuntu 22.04 LTS / 24.04 LTS direkomendasikan untuk VPS)
*   **Web Server**: Nginx (direkomendasikan) atau Apache
*   **PHP**: Versi **8.2** atau lebih baru
*   **Database**:
    *   Opsi A: MySQL 8.0+ / MariaDB 10.6+
    *   Opsi B: **PostgreSQL 14+** (Atau Supabase)
*   **Composer**: Versi 2.x
*   **Node.js & NPM**: Versi 18+ (Untuk build aset frontend)

### Ekstensi PHP yang Wajib Ada:
Pastikan ekstensi berikut aktif di `php.ini`. Jika menggunakan PostgreSQL, pastikan `pgsql` aktif.
- `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`
- `hash`, `intl`, `libxml`, `mbstring`, `openssl`
- `pcre`, `session`, `tokenizer`, `xml`, `zip`
- **Database Driver**:
    - `pdo_mysql` (Jika pakai MySQL)
    - `pdo_pgsql` & `pgsql` (Jika pakai **PostgreSQL/Supabase**)

---

## 2. Struktur Domain & Subdomain

Anda dapat memisahkan aplikasi utama (Frontend/Admin) dengan API menggunakan subdomain.

### Skenario:
- **Domain Utama**: `komiktap.com` (Web & Admin Panel)
- **Subdomain API**: `api.komiktap.com` (Khusus Endpoint API Mobile)

### Konfigurasi DNS (Cloudflare/Provider Domain)
1.  Buat **A Record** untuk `@` (root) ke IP VPS Anda.
2.  Buat **A Record** (atau CNAME) untuk `www` ke IP VPS Anda.
3.  Buat **A Record** untuk `api` (subdomain) ke IP VPS yang **SAMA**.

### Konfigurasi Nginx untuk Subdomain
Anda harus membuat blok server (sites-available) terpisah atau menggunakan satu blok dengan logika tertetu.  **Rekomendasi: Blok Terpisah**.

**File 1: `/etc/nginx/sites-available/komiktap` (Utama)**
```nginx
server {
    listen 80;
    server_name komiktap.com www.komiktap.com;
    root /var/www/komiktap/public;
    # ... konfigurasi standar (lihat bagian Instalasi VPS)
}
```

**File 2: `/etc/nginx/sites-available/komiktap-api` (API)**
```nginx
server {
    listen 80;
    server_name api.komiktap.com;
    root /var/www/komiktap/public; # Root folder SAMA dengan utama
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    # ... sisa konfigurasi php sama dengan yang utama
}
```
*Jangan lupa symlink kedua file ke `sites-enabled` dan restart Nginx.*

### Konfigurasi di Laravel
Pastikan `APP_URL` di `.env` menggunakan domain utama. Untuk API route yang spesifik subdomain, edit `routes/api.php` atau `bootstrap/app.php` (Laravel 11+).

Di Laravel 11/12, Anda bisa mengatur domain routing di `bootstrap/app.php`:
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    apiPrefix: '', // Kosongkan jika ingin langsung app.com/v1 dsb
    // domain: 'api.komiktap.com', // Opsi jika ingin membatasi API hanya via subdomain
)
```

---

## 3. Konfigurasi Database (PostgreSQL & Supabase)

Jika Anda memilih menggunakan **PostgreSQL** (Self-hosted) atau **Supabase** (Managed Cloud PostgreSQL), ikuti panduan ini.

### A. Install PostgreSQL di VPS (Self-Hosted)
```bash
sudo apt install postgresql postgresql-contrib php8.2-pgsql
sudo systemctl start postgresql.service
```
Buat user dan database:
```bash
sudo -u postgres psql
CREATE DATABASE komiktap_db;
CREATE USER komiktap_user WITH PASSWORD 'password_super_kuat';
GRANT ALL PRIVILEGES ON DATABASE komiktap_db TO komiktap_user;
\q
```
Edit `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=komiktap_db
DB_USERNAME=komiktap_user
DB_PASSWORD=password_super_kuat
```

### B. Menggunakan Supabase (Cloud)
Supabase menggunakan PostgreSQL. Ada dua mode koneksi: **Session (Port 5432)** dan **Transaction (Port 6543)**.
Untuk production/serverless environment (seperti Laravel Vapor atau Hosting Shared), disarankan menggunakan **Transaction Mode (Pooling)**. Untuk VPS standar, Session mode biasanya aman, tapi Transaction lebih stabil untuk trafik tinggi.

**Konfigurasi `.env` untuk Supabase:**

```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com  # Ambil dari dashboad Supabase (Connection String > PHP)
DB_PORT=6543                                      # Gunakan 6543 (Transaction) atau 5432 (Session)
DB_DATABASE=postgres                              # Default Supabase biasanya 'postgres'
DB_USERNAME=postgres.abcdefg...                   # Username Project Supabase
DB_PASSWORD=password_proyek_anda
DB_SSLMODE=prefer                                 # Penting untuk koneksi remote
```

**PENTING - Supabase:**
1.  Pastikan Anda menyalin "Connection String" khusus untuk **Laravel/PHP** di dashboard Supabase.
2.  Jika terjadi error "Prepared statement already exists", tambahkan ini di `config/database.php` bagian `pgsql`:
    ```php
    'pgsql' => [
        // ...
        'options' => [
            PDO::ATTR_EMULATE_PREPARES => true,
        ],
    ],
    ```

---

## 4. Instalasi di VPS (Langkah Umum)

Ikuti langkah instalasi standar (Clone, Composer Install, Permission), namun sesuaikan bagian Database Driver.

### Install Driver pgsql (Jika belum)
```bash
sudo apt install php8.2-pgsql
```

### Setup Environment
```bash
cp .env.example .env
nano .env
```
Isi dengan kredensial PostgreSQL/Supabase Anda.

### Migrasi Database
Karena PostgreSQL lebih ketat daripada MySQL, pastikan migrasi berjalan lancar:
```bash
php artisan migrate --force
```

---

## 5. Deployment di Hosting (cPanel) dengan PostgreSQL

Tidak semua Shared Hosting menyediakan PostgreSQL. Jika hosting Anda mendukung:
1.  Cari menu **"PostgreSQL Databases"** di cPanel.
2.  Buat Database dan User seperti biasa.
3.  Pastikan ekstensi PHP `pgsql` atau `pdo_pgsql` dicentang di menu "Select PHP Version".
4.  Hubungkan via `.env` menggunakan `DB_CONNECTION=pgsql`.

Jika hosting hanya support MySQL tapi Anda ingin pakai **Supabase**:
1.  Hosting hanya butuh akses keluar (Outgoing Port 5432/6543).
2.  Pastikan firewall hosting tidak memblokir port tersebut.
3.  Isi `.env` dengan kredensial Supabase.

---

## 6. Keamanan & Best Practices

1.  **SSL/HTTPS**: Wajib untuk semua domain dan subdomain (`komiktap.com` DAN `api.komiktap.com`). Gunakan:
    ```bash
    sudo certbot --nginx -d komiktap.com -d www.komiktap.com -d api.komiktap.com
    ```
2.  **Database External**: Jika pakai Supabase, pastikan password database Anda sangat kuat karena terekspos ke internet (meski dilindungi password).
3.  **Backup Data**: Supabase memiliki fitur backup otomatis (cek plan Anda). Jika self-hosted PostgreSQL, setup cronjob `pg_dump`.

---

## 7. Menggunakan Ngrok (Lokal)

Jika menggunakan Supabase di lokal:
Anda tidak perlu setup DB lokal. Cukup arahkan `.env` lokal ke kredensial Supabase. Semua data development akan masuk ke Supabase (Cloud).

**Hati-hati**: Jangan campur data Production dan Development di database Supabase yang sama. Buat Proyek Supabase terpisah untuk "Staging/Dev" dan "Production".
