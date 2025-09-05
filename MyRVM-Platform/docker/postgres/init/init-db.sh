#!/bin/bash
set -e

echo "==== Database and user setup Start. ===="
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    -- Cek apakah user myrvm_user sudah ada, jika belum, buat
    DO
    \$do\$
    BEGIN
       IF NOT EXISTS (
          SELECT FROM pg_catalog.pg_roles
          WHERE  rolname = 'myrvm_user') THEN

          CREATE ROLE myrvm_user LOGIN PASSWORD 'dr4gonlistio';
       END IF;
    END
    \$do\$;

    -- Database 'myrvm_platform' sudah dibuat oleh variabel environment POSTGRES_DB.
    -- Jadi, kita hanya perlu memberikan hak akses.
    GRANT ALL PRIVILEGES ON DATABASE myrvm_platform TO myrvm_user;
EOSQL
echo "==== Database and user setup completed. ===="
