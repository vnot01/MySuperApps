#!/bin/bash
set -e

echo ">>> Running PostgreSQL init script: init-db.sh"

# Jalankan psql sebagai superuser default 'postgres'
# Variabel POSTGRES_USER dan POSTGRES_DB dari environment TIDAK digunakan di sini
# untuk menghindari konflik. Kita definisikan semuanya secara eksplisit.
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" <<-EOSQL
    -- Buat user aplikasi (jika belum ada)
    DO \$\$
    BEGIN
       IF NOT EXISTS (
          SELECT FROM pg_catalog.pg_roles
          WHERE  rolname = 'myrvm_user') THEN

          CREATE ROLE myrvm_user WITH LOGIN PASSWORD 'dr4gonlistio'; -- Ganti dengan password Anda
       END IF;
    END
    \$\$;

    -- Buat database HANYA JIKA belum ada, dan JADIKAN 'myrvm_user' sebagai OWNER
    -- Perintah ini perlu dijalankan di luar blok DO/BEGIN
    -- Kita cek dulu apakah DB ada, lalu buat jika tidak.
    -- Cara yang lebih mudah adalah membiarkan CREATE DATABASE gagal jika sudah ada,
    -- tetapi kita akan coba cara yang lebih bersih.
    
    -- Berikan izin kepada myrvm_user untuk membuat database (sementara)
    ALTER ROLE myrvm_user CREATEDB;
EOSQL

# Sekarang, jalankan perintah sebagai myrvm_user untuk membuat database
# Ini secara otomatis akan membuat 'myrvm_user' sebagai pemiliknya.
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" -tc "SELECT 1 FROM pg_database WHERE datname = 'myrvm_platform'" | grep -q 1 || psql -v ON_ERROR_STOP=1 --username=myrvm_user --password=myrvm_password -c "CREATE DATABASE myrvm_platform"

# Jalankan lagi sebagai superuser untuk memastikan semua izin lain (jika perlu)
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" <<-EOSQL
    -- (Opsional) Cabut kembali izin membuat database jika tidak diperlukan lagi
    ALTER ROLE myrvm_user NOCREATEDB;
EOSQL

echo ">>> PostgreSQL init script finished successfully."