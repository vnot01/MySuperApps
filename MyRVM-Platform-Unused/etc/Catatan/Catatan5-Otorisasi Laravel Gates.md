**[CATATAN UNTUK DOKUMENTASI]**

**Sub-bagian: Troubleshooting Otorisasi Laravel Gates**

- **Masalah:** Setelah mendefinisikan `Roles`, `Permissions`, dan `Gates` di `AuthServiceProvider`, semua pengecekan `Gate::allows()` mengembalikan `false`, meskipun data di database dan relasi antar model terlihat benar.
- **Proses Debugging:**
  - Verifikasi dilakukan menggunakan `php artisan tinker` untuk memastikan user bisa diambil dari database beserta relasi `role` dan `permissions`-nya.
  - Pengujian di `tinker` menunjukkan bahwa relasi berfungsi (`$admin->role->name` mengembalikan "Admin") dan logika pengecekan permission manual juga berhasil (`$admin->role->permissions->contains('slug', '...')` mengembalikan `true`). Ini mengisolasi masalah pada implementasi `Gate` di `AuthServiceProvider`.
- **Penyebab:** Ditemukan dua penyebab utama di dalam `AuthServiceProvider.php`:
  1.  **Potensi _Lazy Loading_ yang Tidak Andal:** Di dalam konteks eksekusi `Gate`, relasi Eloquent (`$user->role->permissions`) tidak selalu ter-load secara otomatis (lazy loaded) dengan andal.
  2.  **Logika `Gate::before` yang Salah:** `Gate::before` yang dirancang untuk super-admin mungkin mengembalikan `false` secara implisit jika user bukan super-admin, yang akan menghentikan semua pengecekan otorisasi selanjutnya, bukannya melanjutkan ke `Gate::define`.
- **Solusi:**
  1.  **Menerapkan _Eager Loading_ Eksplisit:** Di dalam callback `Gate::before`, ditambahkan `$user->loadMissing('role.permissions');`. Perintah ini secara efisien akan memuat relasi `role` dan sub-relasi `permissions`-nya jika belum di-load, memastikan datanya selalu tersedia untuk semua pengecekan `Gate`.
  2.  **Memperbaiki `Gate::before`:** Logika di dalam `Gate::before` diubah untuk secara eksplisit mengembalikan `null` jika user bukan super-admin. Mengembalikan `null` memberitahu `Gate` untuk melanjutkan pengecekan ke `Gate::define` yang lebih spesifik.

**Cara Pengujian (via `php artisan tinker`):**
Pengujian fungsionalitas Gates dapat dilakukan secara efektif melalui `tinker` untuk mengisolasi logika otorisasi dari lapisan lain (HTTP, Controllers).

1.  **Masuk ke `tinker`:**
    ```bash
    docker compose exec app php artisan tinker
    ```
2.  **Ambil User Contoh:** Ambil instance user untuk setiap peran yang ingin diuji menggunakan query Eloquent.

    ```php
    // Ambil user dengan peran 'Super Admin'
    $superAdmin = App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'super-admin'))->first();

    // Ambil user dengan peran 'Admin'
    $admin = App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'admin'))->first();
    ```

3.  **Jalankan Pengecekan `Gate`:** Gunakan fasad `Gate` dengan metode `forUser()` untuk menguji izin.

    ```php
    // Tes Super Admin (harus bisa melakukan segalanya karena Gate::before)
    Gate::forUser($superAdmin)->allows('manage-tenants');      // Harapan: true
    Gate::forUser($superAdmin)->allows('some-random-ability'); // Harapan: true

    // Tes Admin
    Gate::forUser($admin)->allows('manage-users');             // Harapan: true (sesuai seeder)
    Gate::forUser($admin)->allows('manage-tenants');           // Harapan: false (sesuai seeder)
    ```

**[/AKHIR CATATAN]**

---
