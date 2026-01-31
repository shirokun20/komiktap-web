# Komiktap

**Komiktap** adalah aplikasi web berbasis Laravel yang dirancang untuk platform komik digital. Aplikasi ini dilengkapi dengan panel administrasi modern menggunakan **Filament PHP** dan menyediakan API untuk integrasi dengan aplikasi mobile (Android/iOS).

---

## 🚀 Fitur Utama

-   **Admin Panel (Filament)**: Dashboard manajemen konten yang user-friendly dan responsif.
-   **Manajemen Lisensi**: Sistem untuk mengatur dan memvalidasi lisensi penggunaan aplikasi.
-   **Sistem Donasi**: Fitur untuk menerima donasi dari pengguna (terintegrasi dengan payment gateway).
-   **API Backend**: Menyediakan endpoint RESTful untuk konsumsi data dari aplikasi mobile.
-   **Manajemen Pengguna & Role**: Kontrol akses bertingkat untuk administrator dan pengguna biasa.
-   **Statistik Dashboard**: Pantau total download, pendapatan, dan aktivitas pengguna secara realtime.

## 🛠️ Teknologi yang Digunakan

-   **Backend Framework**: Laravel 12.x
-   **Admin Panel**: Filament PHP 3.x
-   **Database**: MySQL / MariaDB / PostgreSQL (Supabase compatible)
-   **Frontend Assets**: TailwindCSS, Vite
-   **PHP Version**: 8.2+

---

## 📥 Instalasi Lokal (Development)

Ikuti langkah berikut untuk menjalankan aplikasi di komputer lokal Anda:

1.  **Clone Repository**
    ```bash
    git clone https://github.com/username/komiktap.git
    cd komiktap
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    npm install
    ```

3.  **Setup Environment**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Sesuaikan konfigurasi database di file `.env`.*

4.  **Migrasi Database**
    ```bash
    php artisan migrate --seed
    ```

5.  **Buat User Admin**
    ```bash
    php artisan make:filament-user
    ```

6.  **Jalankan Aplikasi**
    ```bash
    npm run dev
    # Buka terminal baru
    php artisan serve
    ```
    Akses aplikasi di `http://localhost:8000/admin`.

---

## 🌐 Dokumentasi Deployment

Untuk panduan lengkap mengenai cara deploy aplikasi ini ke **VPS** atau **Hosting**, silakan baca dokumen berikut:

👉 **[PANDUAN DEPLOYMENT LENGKAP](./DEPLOYMENT.md)**

Dokumen tersebut membahas:
- Persyaratan Server (VPS/Hosting).
- Instalasi langkah demi langkah.
- Keamanan (SSL, Firewall, Debug mode).
- Cara memisahkan Domain Utama dan Domain API.
- Cara menggunakan **Ngrok** untuk testing publik.

---

## 📝 Lisensi

Aplikasi ini bersifat **Private / Proprietary**. Dilarang mendistribusikan ulang tanpa izin pemilik hak cipta.
