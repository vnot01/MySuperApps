**[CATATAN UNTUK DOKUMENTASI]**

**Sub-bagian: Troubleshooting Kritis pada Fase 1 (Docker & Database)**

Selama proses setup fondasi, beberapa masalah krusial berhasil diidentifikasi dan diselesaikan:

1.  **Masalah: Sinkronisasi Startup Kontainer (_Race Condition_)**

    - **Gejala:** Kontainer `app` (Laravel) mencoba menjalankan `php artisan migrate` atau `cache:clear` sebelum kontainer `db` (PostgreSQL) selesai menginisialisasi database. Hal ini menyebabkan error `FATAL: database "..." does not exist`.
    - **Solusi:** Mengimplementasikan mekanisme "tunggu database" di dalam skrip `entrypoint.sh` pada kontainer `app`. Skrip ini sekarang berisi dua loop `until`:
      1.  Loop pertama menggunakan `nc` (netcat) untuk menunggu sampai port PostgreSQL (`5432`) terbuka dan menerima koneksi.
      2.  Loop kedua menggunakan `psql` (PostgreSQL client) untuk secara aktif mencoba terhubung ke database spesifik (`myrvm_platform`) dengan user aplikasi (`myrvm_user`). Perintah Artisan hanya akan dilanjutkan setelah koneksi ini berhasil, memastikan database benar-benar siap.
    - **Dependensi:** Solusi ini mengharuskan paket `postgresql-client` (yang menyediakan `psql`) diinstal di dalam image Docker `app` melalui `Dockerfile`.

2.  **Masalah: Inisialisasi Database PostgreSQL & Hak Akses Skema**

    - **Gejala:** `php artisan migrate` gagal dengan error `SQLSTATE[42501]: Insufficient privilege: 7 ERROR: permission denied for schema public`.
    - **Penyebab:** User aplikasi (`myrvm_user`) yang dibuat tidak memiliki kepemilikan (ownership) atau izin `CREATE` pada skema `public` di dalam database.
    - **Solusi:** Menyederhanakan proses inisialisasi dengan mengandalkan mekanisme bawaan dari image Docker `postgres`.
      - Skrip kustom `init-db.sh` **dihapus**.
      - `docker-compose.yml` untuk service `db` dikonfigurasi dengan variabel environment `POSTGRES_DB`, `POSTGRES_USER`, dan `POSTGRES_PASSWORD`, menggunakan nilai dari kredensial aplikasi (misalnya, `myrvm_platform`, `myrvm_user`).
      - Dengan metode ini, entrypoint internal dari image `postgres` akan secara otomatis membuat database dan user yang ditentukan, dan yang terpenting, **menjadikan user tersebut sebagai pemilik (owner) database**, yang secara implisit memberikan semua hak akses yang diperlukan.

3.  **Masalah: Kegagalan Memuat Konfigurasi `.env` untuk MinIO/S3**
    - **Gejala:** Perintah `tinker` menunjukkan `config('filesystems.disks.s3')` mengembalikan nilai `null` untuk semua kunci (bucket, key, secret, dll.). Ini menyebabkan error `Undefined method 'url'` atau `Class ... not found` saat `Storage` facade digunakan.
    - **Penyebab:** File cache konfigurasi Laravel yang "bandel" (`bootstrap/cache/config.php`) di dalam volume Docker.
    - **Solusi:** Memastikan `entrypoint.sh` menjalankan `php artisan config:clear` sebagai salah satu langkah pertamanya (sebelum perintah Artisan lain yang mungkin bergantung pada konfigurasi). Ini memaksa Laravel untuk membaca ulang file `.env` setiap kali kontainer dimulai, yang sangat berguna di lingkungan pengembangan.

**[/AKHIR CATATAN]**
