Sangat baik. Visi baru telah ditetapkan. Sekarang mari kita terjemahkan visi tersebut menjadi sebuah rencana implementasi yang terstruktur, mendalam, dan bertingkat, beserta peta jalan progres yang baru.

Dokumen ini akan menjadi panduan teknis kita untuk membangun **MyRVM v2.0**.

---

### **Rencana Implementasi & Peta Jalan Progres: MyRVM v2.0**

Rencana ini dibagi menjadi beberapa fase utama, di mana setiap fase membangun fondasi untuk fase berikutnya. Beberapa sub-tahap dalam fase yang berbeda dapat dikerjakan secara paralel jika sumber daya memungkinkan.

---

### **Fase 1: Fondasi & Arsitektur Inti (Progres: 0% -> 20%)**

**Tujuan:** Menyiapkan lingkungan pengembangan yang bersih, membangun fondasi backend dengan Docker, dan mendefinisikan skema database baru yang mendukung arsitektur multi-tenant dan multi-peran.

- **1.1. Inisialisasi Proyek Backend (`MyRVM-Platform`)**

  - **Aksi:** Buat proyek Laravel 12 baru (`laravel new MyRVM-Platform`).
  - **Detail:** Inisialisasi repositori Git, lakukan commit awal.

- **1.2. Konfigurasi Lingkungan Docker Terpusat**

  - **Aksi:** Buat file `docker-compose.yml` di root proyek.
  - **Detail:**
    - Definisikan service `app` (PHP-FPM) menggunakan `Dockerfile` kustom.
    - Definisikan service `web` (Nginx) yang akan me-reverse-proxy ke `app`.
    - Definisikan service `db` (menggunakan image `postgres:latest`).
    - Definisikan service `minio` (menggunakan image `minio/minio:latest`).
    - Buat jaringan kustom (misalnya `myrvm_network`) untuk semua service.
    - Definisikan _named volumes_ untuk persistensi data PostgreSQL dan MinIO.

- **1.3. Pembuatan `Dockerfile` untuk Laravel 12**

  - **Aksi:** Buat `Dockerfile` di root proyek.
  - **Detail:**
    - Gunakan base image `php:8.2-fpm-alpine` (atau versi PHP terbaru yang didukung Laravel 12).
    - Instal semua dependensi sistem yang dibutuhkan: `postgresql-dev` (untuk driver `pdo_pgsql`), `libzip-dev`, `libpng-dev`, `imagemagick-dev`, `su-exec`, dll.
    - Instal semua ekstensi PHP yang dibutuhkan: `pdo_pgsql`, `zip`, `gd`, `imagick`, `intl`, dll.
    - Instal Composer.
    - Siapkan skrip `entrypoint.sh` yang akan menangani permission dan inisialisasi runtime.

- **1.4. Inisialisasi Database Otomatis (PostgreSQL)**

  - **Aksi:** Buat skrip `init-db.sh` di `docker/postgres/init/`.
  - **Detail:**
    - Skrip ini akan membuat database dan user khusus untuk aplikasi MyRVM (`myrvm_db`, `myrvm_user`).
    - Di `docker-compose.yml`, mount direktori `docker/postgres/init/` ke `/docker-entrypoint-initdb.d` di service `db`.
  - **Eksekusi:** Jalankan `docker compose up -d db`. Verifikasi bahwa database dan user telah dibuat.

- **1.5. Desain & Migrasi Skema Database Baru**
  - **Aksi:** Buat file-file migrasi Laravel.
  - **Detail:**
    - Buat migrasi untuk tabel `tenants`.
    - Modifikasi migrasi `users` untuk menambahkan `role_id` (FK), `tenant_id` (FK, nullable), dan hapus `role` enum lama.
    - Buat migrasi untuk tabel `roles` dan `permissions` (serta tabel pivot `role_permission`).
    - Buat migrasi untuk tabel ekonomi: `user_balances`, `transactions`, `vouchers`, `voucher_redemptions`.
    - Buat migrasi untuk tabel `deposits` dengan penyesuaian (misalnya, `image_path` opsional, `local_ai_result` JSON).
  - **Goal:** Skema database yang lengkap dan siap untuk mendukung semua fitur baru.

**Goal Fase 1:** Sebuah stack Docker yang berjalan berisi Laravel 12, Nginx, PostgreSQL, dan MinIO. Aplikasi Laravel dapat terhubung ke database PostgreSQL, dan skema database baru telah dimigrasikan.

---

### **Fase 2: Backend - Autentikasi Multi-Peran & API Inti (Progres: 20% -> 50%)**

**Tujuan:** Membangun sistem autentikasi dan otorisasi yang kuat serta API dasar yang akan dikonsumsi oleh semua frontend.

- **2.1. Implementasi Role & Permission**

  - **Aksi:** Buat model `Role`, `Permission`, `Tenant`. Definisikan relasi di model `User`.
  - **Detail:** Gunakan **Laravel Gates & Policies** untuk mendefinisikan hak akses. Misalnya, `TenantPolicy` (hanya super admin yang bisa membuat), `UserPolicy` (admin bisa mengelola user di tenant-nya).

- **2.2. Implementasi Autentikasi Email & Verifikasi**

  - **Aksi:** Implementasikan _contract_ `MustVerifyEmail` pada model `User`.
  - **Detail:** Konfigurasikan mailer di `.env`. Buat alur registrasi yang mengirim email verifikasi. Lindungi rute-rute penting dengan middleware `verified`.

- **2.3. Implementasi Login Sosial (Google, LINE, Discord)**

  - **Aksi:** Instal Laravel Socialite. Cari dan instal driver pihak ketiga yang andal untuk LINE dan Discord dari "Socialite Providers".
  - **Detail:** Buat rute dan metode controller untuk setiap provider (redirect dan callback). Tangani logika untuk membuat user baru atau login user yang sudah ada berdasarkan email/ID sosial.

- **2.4. Pengembangan API CRUD Dasar**
  - **Aksi:** Buat controller API yang dilindungi oleh Sanctum dan otorisasi dari Gates/Policies.
  - **Detail:**
    - **Super Admin API:** `GET /api/v2/tenants`, `POST /api/v2/tenants`, dll.
    - **Admin API:** `GET /api/v2/users`, `POST /api/v2/users`, dll. (di-scope ke tenant admin).
    - **User API:** `GET /api/v2/profile`, `POST /api/v2/profile/update`, dll.
  - **Pengujian:** Uji semua endpoint ini menggunakan Postman dengan token Sanctum dari user dengan peran yang berbeda.

**Goal Fase 2:** Sistem autentikasi dan otorisasi berfungsi penuh untuk semua peran. API dasar untuk manajemen tenant dan pengguna sudah siap dan teruji.

---

### **Fase 3: Edge AI & Aplikasi Kontrol RVM (`MyRVM-EdgeControl`) (Progres: 50% -> 70%)**

**Tujuan:** Mengembangkan aplikasi Python yang berjalan di Jetson Orin Nano, yang mampu melakukan inferensi AI lokal dan berkomunikasi dengan backend.

- **3.1. Setup Lingkungan Jetson**

  - **Aksi:** Flash Jetson Orin Nano dengan JetPack terbaru. Instal dependensi sistem.
  - **Detail:** Buat lingkungan virtual Python. Instal `torch`, `ultralytics`, `opencv-python`, `pyserial`, `requests`, dan `gpiod`.

- **3.2. Integrasi Model AI Lokal**

  - **Aksi:** Muat file `best.pt` (YOLO & SAM) ke dalam aplikasi Python.
  - **Detail:** Tulis pipeline inferensi:
    1.  Fungsi `capture_image()`.
    2.  Fungsi `run_yolo(image)` -> mengembalikan `boxes`, `classes`.
    3.  Fungsi `run_sam(image, boxes)` -> mengembalikan `masks`.
    4.  Fungsi `analyze_masks(masks)` -> mengembalikan `condition`, `volume_estimate`.
    5.  Fungsi `package_results(yolo_results, analysis_results)` -> membuat payload JSON.

- **3.3. Komunikasi dengan Backend & Hardware**
  - **Aksi:** Implementasikan pemanggilan API dan kontrol GPIO.
  - **Detail:**
    - Fungsi `validate_qr_code(qr_data)` yang memanggil `POST /api/v2/rvm/validate-token`.
    - Fungsi `submit_deposit(json_payload)` yang memanggil `POST /api/v2/rvm/deposit`.
    - Gunakan library `gpiod` untuk mengontrol motor stepper (PUL, DIR, ENA) dan membaca sensor.

**Goal Fase 3:** Aplikasi Python di Jetson dapat secara mandiri mengambil gambar, menganalisisnya menggunakan YOLO+SAM, dan mengirimkan hasil analisis terstruktur ke backend API.

---

### **Fase 4: Pengembangan Frontend (Dasbor & Aplikasi Mobile) (Progres: 70% -> 90%)**

**Tujuan:** Membangun semua antarmuka pengguna yang dibutuhkan oleh setiap peran.

- **4.1. Dasbor Super Admin & Admin (Blade + Vue)**

  - **Aksi:** Buat layout Blade, rute web, dan controller untuk menyajikan halaman dasbor.
  - **Detail:**
    - **Super Admin:** Halaman untuk CRUD Tenant. Dasbor analitik global.
    - **Admin:** Halaman untuk CRUD User (di dalam tenant-nya), CRUD RVM, melihat transaksi.
    - Gunakan komponen Vue.js untuk bagian-bagian interaktif seperti tabel data (dengan sorting/filtering) dan grafik.

- **4.2. Dasbor & Aplikasi Tenant (Blade + Vue & Flutter)**

  - **Aksi:** Rancang dan bangun dasbor web dan aplikasi mobile untuk tenant.
  - **Detail:** Fokus pada fungsionalitas inti: melihat statistik penukaran voucher, mengelola penawaran voucher, dan profil tenant.

- **4.3. Aplikasi User (`MyRVM-UserApp` - Flutter)**
  - **Aksi:** Kembangkan aplikasi Flutter untuk pengguna akhir.
  - **Detail:**
    - Implementasikan semua layar: Login/Register (termasuk LINE & Discord), Dasbor (menampilkan saldo), Halaman Penukaran Voucher, Riwayat Transaksi, Profil.
    - Integrasikan dengan API dari Fase 2.

**Goal Fase 4:** Semua antarmuka pengguna untuk semua peran telah selesai dibangun dan terhubung ke API backend.

---

### **Fase 5: Mesin Ekonomi & Sistem Reward (Progres: 90% -> 97%)**

**Tujuan:** Mengimplementasikan logika bisnis inti untuk sistem saldo dan voucher.

- **5.1. Backend: Logika Transaksi**

  - **Aksi:** Buat _Service Class_ di Laravel untuk menangani logika ekonomi.
  - **Detail:**
    - Metode `creditUserBalance(userId, amount, reason)` setelah deposit divalidasi.
    - Metode `redeemVoucher(userId, voucherId)` yang akan memeriksa saldo, mengurangi saldo, mengurangi stok voucher, dan mencatat transaksi.
    - **Gunakan _Database Transactions_ Laravel (`DB::transaction()`)** untuk semua operasi yang mengubah saldo dan stok untuk memastikan konsistensi data.

- **5.2. Frontend: Integrasi Fitur Ekonomi**
  - **Aksi:** Hubungkan logika backend ke antarmuka pengguna.
  - **Detail:**
    - Pastikan saldo pengguna di Aplikasi User diperbarui secara _real-time_ (atau dengan _pull-to-refresh_) setelah deposit.
    - Bangun alur penukaran voucher di Aplikasi User.

**Goal Fase 5:** Sistem ekonomi internal berfungsi penuh, memungkinkan pengguna menerima saldo dan menukarkannya dengan voucher dari tenant.

---

### **Fase 6: Integrasi, Pengujian Menyeluruh & Deployment (Progres: 97% -> 100%)**

**Tujuan:** Memastikan semua komponen sistem bekerja sama dengan mulus dan mendeploynya ke lingkungan produksi.

- **6.1. Pengujian End-to-End:**
  - **Aksi:** Lakukan simulasi alur kerja penuh: Registrasi user -> Login -> Setor item di RVM -> Inferensi AI di Jetson -> Pengiriman hasil ke backend -> Saldo user bertambah -> User menukar voucher -> Tenant melihat penukaran.
- **6.2. Persiapan Produksi:**
  - **Aksi:** Finalisasi `Dockerfile` dan `docker-compose.yml` untuk produksi (misalnya, menghapus volume mount untuk kode, menjalankan perintah cache Laravel).
  - Siapkan file `.env.production`.
- **6.3. Deployment ke Server `vnotpanel.my.id`:**
  - **Aksi:** Tarik kode `MyRVM-Platform` terbaru ke server.
  - Jalankan `docker compose build` dan `docker compose up -d`.
  - Konfigurasikan Nginx Proxy Manager dan Cloudflare Tunnel untuk semua domain yang relevan.
- **6.4. Go-Live:**
  - Lakukan pengujian akhir di lingkungan produksi.
  - Mulai monitoring dengan Uptime Kuma.
