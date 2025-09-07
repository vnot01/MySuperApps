Dokumen ini akan menjadi panduan teknis utama kita untuk membangun **MyRVM v2.1**.

---

### **Rencana Implementasi Detail & Peta Jalan Progres: MyRVM v2.1**

Rencana ini disusun secara bertingkat, dari fondasi backend hingga ke implementasi di perangkat edge dan aplikasi pengguna. Setiap fase memiliki tujuan dan hasil yang jelas.

---

### **Fase 1: Fondasi Backend, Database & WebSocket (Progres: 0% -> 30%)**

**Tujuan:** Menyiapkan seluruh infrastruktur backend yang diperlukan untuk mendukung semua fitur baru, termasuk database, API sesi, dan komunikasi real-time.

- **1.1. Inisialisasi Proyek & Lingkungan Docker**

  - **Aksi:**
    1.  Buat proyek Laravel 12 baru: `laravel new MyRVM-Platform`.
    2.  Inisialisasi repositori Git.
    3.  Buat `Dockerfile` (dengan `php-pgsql`, `su-exec`, dll.).
    4.  Buat `docker-compose.yml` yang mendefinisikan service `app`, `web`, `minio_prod`, dan menghubungkan semuanya ke jaringan eksternal `core-network`. **Service database tidak didefinisikan di sini.**
    5.  Buat skrip `entrypoint.sh` untuk menangani permission dan inisialisasi runtime.
  - **Hasil:** Kerangka proyek backend dan konfigurasi Docker dasar sudah siap.

- **1.2. Setup Database & Skema Baru**

  - **Aksi:**
    1.  Masuk ke kontainer `postgres_db` dari `core-services` dan buat database `myrvm_db` beserta user `myrvm_user`.
    2.  Di proyek Laravel, konfigurasikan file `.env` untuk terhubung ke `DB_HOST=postgres_db`.
    3.  Buat semua file migrasi yang diperlukan: `tenants`, `roles`, `permissions`, `users` (yang dimodifikasi), `user_balances`, `transactions`, `vouchers`, `voucher_redemptions`, dan `deposits` (yang dimodifikasi).
    4.  Jalankan `php artisan migrate`.
  - **Hasil:** Skema database yang lengkap dan sesuai dengan visi baru telah dibuat di instance PostgreSQL terpusat.

- **1.3. Implementasi Backend WebSocket (Laravel Reverb)**

  - **Aksi:**
    1.  Instal Laravel Reverb: `composer require laravel/reverb`.
    2.  Jalankan `php artisan reverb:install`.
    3.  Konfigurasikan `.env` dengan `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`. (Gunakan `php artisan reverb:secret` untuk generate).
    4.  Tambahkan service `reverb` ke `docker-compose.yml` MyRVM, menggunakan image yang sama dengan service `app`, tetapi dengan `command: php artisan reverb:start --host=0.0.0.0`.
    5.  Pastikan `BROADCAST_CONNECTION=reverb` di `.env`.
  - **Hasil:** Server WebSocket berjalan dan siap menerima koneksi.

- **1.4. Definisi Channel & Event Broadcasting**
  - **Aksi:**
    1.  Buat _channel_ otorisasi di `routes/channels.php`, misalnya `Broadcast::channel('rvm.{rvmId}', function ($user, $rvmId) { ... });`. Awalnya, otorisasi bisa dibuat sederhana.
    2.  Buat _Event Class_ di Laravel untuk setiap komunikasi real-time, misalnya `SesiDiotorisasi`, `AnalisisSelesai`, `PerintahUntukRVM`. Pastikan setiap event mengimplementasikan `ShouldBroadcast`.
  - **Hasil:** Mekanisme untuk mengirim dan menerima pesan real-time antara backend dan klien telah didefinisikan.

**Goal Fase 1:** Backend Laravel 12 berjalan di Docker, terhubung ke database PostgreSQL global, dengan server WebSocket (Reverb) yang fungsional dan event-event dasar yang telah didefinisikan.

---

### **Fase 2: Backend - API Sesi & Logika Bisnis Inti (Progres: 30% -> 60%)**

**Tujuan:** Membangun semua endpoint API yang diperlukan untuk alur kerja baru dan logika bisnis yang mendukungnya.

- **2.1. Pengembangan API Manajemen Sesi RVM**

  - **Aksi:** Buat `RvmSessionController.php`.
  - **Detail:**
    - Implementasikan `GET /api/v2/rvm/session/create`: Menghasilkan token sesi unik, menyimpannya di cache (Redis atau database) dengan status `menunggu_otorisasi` dan `rvm_id`, lalu mengembalikannya.
    - Implementasikan `POST /api/v2/rvm/session/claim`: Menerima token sesi (dari Aplikasi User) dan token otentikasi user. Memvalidasi keduanya, mengubah status sesi menjadi `diotorisasi`, mengaitkan `user_id`, lalu **menyiarkan event `SesiDiotorisasi`**.
    - Implementasikan `POST /api/v2/rvm/session/activate-guest`: Menerima token sesi, mengubah statusnya menjadi `aktif_sebagai_tamu`, lalu **menyiarkan event `SesiTamuAktif`**.

- **2.2. Penyesuaian API Deposit & Logika AI**

  - **Aksi:** Modifikasi `DepositController.php`.
  - **Detail:**
    - Endpoint `POST /api/v2/rvm/deposit` sekarang menerima **hasil analisis terstruktur (JSON)** dari RVM, bukan lagi file gambar.
    - Implementasikan logika untuk menyimpan hasil analisis lokal ini.
    - Buat logika kondisional: Jika kepercayaan hasil analisis lokal rendah, panggil `GeminiVisionService` untuk validasi sekunder (ini memerlukan pengiriman gambar asli juga).
    - Setelah deposit divalidasi, panggil _Service Class_ ekonomi untuk mengkreditkan saldo pengguna.
    - **Siarkan event `AnalisisSelesai`** dengan hasilnya.

- **2.3. Pengembangan API Ekonomi (Saldo & Voucher)**
  - **Aksi:** Buat `BalanceController.php` dan `VoucherController.php`.
  - **Detail:**
    - `GET /api/v2/user/balance`: Mengembalikan saldo pengguna saat ini.
    - `GET /api/v2/vouchers`: Mengembalikan daftar voucher yang tersedia dari semua tenant.
    - `POST /api/v2/vouchers/redeem`: Menerima `voucher_id`, memanggil _Service Class_ ekonomi untuk memproses penukaran.

**Goal Fase 2:** Semua endpoint API yang dibutuhkan untuk alur kerja baru (sesi RVM, deposit, dan ekonomi) telah selesai dan teruji menggunakan Postman. Backend siap untuk dihubungkan dengan semua jenis frontend.

---

### **Fase 3: Antarmuka & Kontrol RVM (Progres: 60% -> 75%)**

**Tujuan:** Membuat antarmuka web yang berjalan di layar sentuh RVM dan aplikasi Python yang menjadi jembatan ke perangkat keras.

- **3.1. Pengembangan Antarmuka "Front Office" (Blade + Vue)**

  - **Aksi:** Buat rute web, controller, dan view (Blade + Vue) untuk `/rvm-ui/{rvm}`.
  - **Detail:**
    - **Saat Halaman Dimuat:** Komponen Vue melakukan panggilan API ke `session/create` untuk mendapatkan token dan menampilkannya sebagai QR code.
    - **Koneksi WebSocket:** Komponen Vue (menggunakan Laravel Echo & Echo JS) terhubung ke server Reverb dan mendengarkan di _channel_ `RvmChannel.{rvm_id}`.
    - **Reaktivitas UI:** Tulis logika untuk mengubah tampilan UI berdasarkan event yang diterima (misalnya, `SesiDiotorisasi` -> tampilkan nama user, `AnalisisSelesai` -> tampilkan hasil).
    - **Interaksi Tombol:** Tombol "Lanjutkan sebagai Tamu" memanggil API `session/activate-guest`.

- **3.2. Pengembangan Aplikasi Jembatan (`MyRVM-EdgeControl` - Python)**
  - **Aksi:** Kembangkan aplikasi Python untuk berjalan di Jetson Orin Nano.
  - **Detail:**
    - **Startup:** Meluncurkan browser Chromium dalam mode kiosk ke URL "Front Office".
    - **Koneksi WebSocket:** Implementasikan klien WebSocket yang terhubung ke server Reverb dan mendengarkan di _channel_ yang sama.
    - **Event Listener:** Tulis logika untuk merespons event dari backend (misalnya, `SesiDiotorisasi` -> `buka_pintu()`).
    - **Kontrol Perangkat Keras:**
      - Fungsi `buka_pintu()`, `tutup_pintu()`, `sortir_item(jenis)` yang berkomunikasi dengan ESP32 via serial.
      - Fungsi `ambil_gambar_dan_analisis()`: Mengambil gambar, menjalankan pipeline YOLO+SAM, dan memanggil API `deposit` dengan hasilnya.
    - **Pelaporan Sensor:** Jika sensor mendeteksi item, aplikasi Python mengirim event WebSocket `ItemTerdeteksi` ke backend.

**Goal Fase 3:** Antarmuka di layar sentuh RVM dapat menampilkan QR, merespons otorisasi dari pengguna secara _real-time_, dan aplikasi Python di latar belakang dapat menerima perintah dari backend (via WebSocket) untuk mengontrol perangkat keras.

---

### **Fase 4: Aplikasi Pengguna & Tenant (Progres: 75% -> 95%)**

**Tujuan:** Membangun aplikasi mobile untuk pengguna akhir dan tenant.

- **4.1. Pengembangan Ulang Aplikasi User (`MyRVM-UserApp` - Flutter)**

  - **Aksi:** Hapus UI lama untuk menampilkan QR. Tambahkan fitur pemindai QR.
  - **Detail:**
    - Buat layar "Pindai RVM" yang membuka kamera.
    - Setelah QR dari layar RVM dipindai, aplikasi memanggil `POST /api/v2/rvm/session/claim`.
    - Bangun layar "Tukar Voucher" yang memanggil API `vouchers/redeem`.
    - Pastikan Dasbor menampilkan saldo, bukan lagi poin.

- **4.2. Pengembangan Aplikasi Tenant (`MyRVM-TenantApp` - Flutter)**

  - **Aksi:** Buat proyek Flutter baru.
  - **Detail:**
    - Buat alur login khusus untuk pengguna dengan peran 'Tenant'.
    - Buat dasbor sederhana yang menampilkan statistik penukaran voucher mereka.
    - (Fitur Lanjutan) Form untuk membuat penawaran voucher baru.

- **4.3. Pengembangan Dasbor Web (Super Admin, Admin, Tenant)**
  - **Aksi:** Bangun halaman-halaman yang dibutuhkan di `MyRVM-Platform` menggunakan Blade dan Vue.
  - **Detail:**
    - Implementasikan halaman manajemen multi-tenant.
    - Buat dasbor analitik yang menampilkan data dari tabel `transactions` dan `vouchers`.

**Goal Fase 4:** Semua antarmuka pengguna (mobile dan web) untuk semua peran (User, Tenant, Admin, Super Admin) telah selesai dan berfungsi.

---

### **Fase 5: Pengujian, Penyempurnaan & Deployment (Progres: 95% -> 100%)**

**Tujuan:** Memastikan seluruh ekosistem bekerja secara harmonis dan mendeploynya ke lingkungan produksi.

- **5.1. Pengujian End-to-End:** Lakukan pengujian menyeluruh untuk semua alur kerja yang telah dirancang.
- **5.2. Finalisasi Konfigurasi Produksi:**
  - Siapkan file `.env.production`.
  - Finalisasi `Dockerfile` dan `docker-compose.yml` untuk produksi.
- **5.3. Deployment ke Server `vnotpanel.my.id`:**
  - Ikuti rencana deployment yang telah kita diskusikan: `git pull`, `docker compose build`, `docker compose up`, konfigurasi Nginx Proxy Manager dan Cloudflare Tunnel.
- **5.4. Go-Live & Monitoring:**
  - Lakukan pengujian akhir di domain publik.
  - Siapkan Uptime Kuma untuk memonitor semua endpoint publik.
