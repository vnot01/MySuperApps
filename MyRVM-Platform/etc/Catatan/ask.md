saya sudah berhasil membuat database myrvm_platform di posgre.

1. ini docker-compose.yml saya:

```yml
services:
  # Service Aplikasi Laravel (PHP-FPM)
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: myrvm_app_dev
    restart: unless-stopped
    working_dir: /var/www/html
    environment:
      #   STARTUP_COMMAND_1: sudo chown docker /proc/self/fd/{1,2}
      CONTAINER_ROLE: app # Menandai kontainer ini sebagai 'app'
      DB_HOST: ${DB_HOST:-db}
      DB_DATABASE: ${DB_DATABASE:-myrvm_platform}
      DB_USERNAME: ${DB_USERNAME:-myrvm_user}
      DB_PASSWORD: ${DB_PASSWORD:-myrvm_password}
    volumes:
      - ./:/var/www/html
    networks:
      - myrvm_network
    depends_on:
      - db
      - minio

  # Service Web Server (Nginx)
  web:
    image: nginx:alpine
    container_name: myrvm_web_dev
    restart: unless-stopped
    ports:
      - "8000:80" # Akses aplikasi via http://localhost:8000
    volumes:
      - ./:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    networks:
      - myrvm_network
    depends_on:
      - app

  # Service Database (PostgreSQL)
  db:
    image: postgres:latest
    container_name: myrvm_db_dev
    restart: unless-stopped
    ports:
      - "54321:5432"
    environment:
      POSTGRES_DB: ${DB_DATABASE:-myrvm_platform}
      POSTGRES_USER: ${POSTGRES_USER:-postgres} # Ini adalah user SUPERUSER postgres
      POSTGRES_PASSWORD: ${POSTGRES_PASSWORD:-password} # Ini adalah password SUPERUSER postgres
      PGDATA: /var/lib/postgresql/data
    volumes:
      - myrvm_postgres_data:/var/lib/postgresql/data
      # --- TAMBAHKAN BARIS INI ---
      - ./docker/postgres/init:/docker-entrypoint-initdb.d
    networks:
      - myrvm_network

    # Tambahkan healthcheck untuk memastikan PostgreSQL siap sebelum 'app' mulai
    # Ini membantu mencegah masalah koneksi database saat startup
    healthcheck:
      test:
        [
          "CMD-SHELL",
          "pg_isready -d $${POSTGRES_DB} -U $${POSTGRES_USER:-postgres}",
        ]
      interval: 10s
      retries: 5
    # healthcheck:
    #   test: ["CMD-SHELL", "pg_isready -U ${POSTGRES_USER:-postgres}"]
    #   interval: 10s

  # Service Penyimpanan Objek (MinIO)
  minio:
    image: minio/minio:latest
    container_name: myrvm_minio_dev
    restart: unless-stopped
    ports:
      - "9000:9000" # MinIO API
      - "9001:9001" # MinIO Console (UI) -> akses via http://localhost:9001
    environment:
      MINIO_ROOT_USER: ${MINIO_ROOT_USER:-minioadmin}
      MINIO_ROOT_PASSWORD: ${MINIO_ROOT_PASSWORD:-minioadminsecret}
    volumes:
      - myrvm_minio_data:/data
    command: server /data --console-address ":9001"
    networks:
      - myrvm_network

  reverb:
    image: myrvm-platform-app:latest # Menggunakan image yang sama dengan 'app'
    container_name: myrvm_reverb_dev
    restart: unless-stopped
    command: php artisan reverb:start --host=0.0.0.0 --port=8080
    volumes:
      - ./:/var/www/html # Mount kode agar bisa membaca perubahan .env dan config
    ports:
      - "8080:8080" # Map port Reverb ke host untuk debugging/koneksi langsung
    networks:
      - myrvm_network
    depends_on:
      - app # Bergantung pada 'app' untuk kode Laravel

# Definisi Jaringan Kustom
networks:
  myrvm_network:
    driver: bridge

# Definisi Volume Persisten
volumes:
  myrvm_postgres_data:
    driver: local
  myrvm_minio_data:
    driver: local
```

2. ini entrypoint.sh saya:

```sh
#!/bin/sh
set -e

# Cek variabel environment untuk menentukan peran kontainer
# Kita akan set CONTAINER_ROLE=app di docker-compose.yml untuk service app
if [ "$CONTAINER_ROLE" = "app" ]; then
    echo "Entrypoint: Container role is 'app'. Running full setup..."
    # --- TAMBAHKAN BLOK 'WAIT FOR POSTGRES' INI ---
    # -----------------------------------------------
    # Langkah 1: Tunggu sampai port PostgreSQL terbuka
    echo "Entrypoint: Waiting for PostgreSQL port 5432 to open..."
    # Loop sampai koneksi ke DB_HOST (db) di port 5432 berhasil
    # Gunakan nc (netcat) yang ada di Alpine. Opsi -z akan scan port tanpa mengirim data.
    until nc -z -v -w30 db 5432; do
      echo "Waiting for database connection..."
      sleep 2
    done
    echo "Entrypoint: PostgreSQL port is open."
    # Langkah 2: Tunggu sampai database 'myrvm_platform' benar-benar dibuat oleh init-db.sh
    echo "Entrypoint: Waiting for database 'myrvm_platform' to be created..."
    # Kita menggunakan psql untuk mencoba listing database sampai berhasil
    # Variabel PG* diambil dari .env Laravel yang di-mount
    export PGPASSWORD=$DB_PASSWORD
    until psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -c '\q'; do
      >&2 echo "Postgres is unavailable - sleeping"
      sleep 2
    done
    >&2 echo "Postgres is up and database is ready - executing command"

    # Sekarang kita 100% yakin database dan user sudah siap
    # Langkah 3: Jalankan setup aplikasi Laravel
    if [ "$(id -u)" = '0' ]; then
        echo "Entrypoint (Role: App): Running as root, setting up environment..."
        # ... (semua logika chown, mkdir, artisan migrate, key:generate, dll. masuk ke sini) ...
        # echo "Entrypoint: Script started. Running as root."
        ############## Bagian setup aplikasi Laravel ##############
        # 1. Atur kepemilikan dan permission untuk storage dan bootstrap/cache
        # echo "Entrypoint: Setting permissions for storage and bootstrap/cache..."
        # Buat direktori yang diperlukan jika belum ada
        mkdir -p /var/www/html/storage/framework/sessions
        mkdir -p /var/www/html/storage/framework/views
        mkdir -p /var/www/html/storage/framework/cache/data
        mkdir -p /var/www/html/storage/logs
        mkdir -p /var/www/html/bootstrap/cache

        # Atur kepemilikan dan permission
        chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
        chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

        # 2. Tangani file .env
        if [ ! -f "/var/www/html/.env" ]; then
            echo "Entrypoint: .env not found, copying from .env.example..."
            cp /var/www/html/.env.example /var/www/html/.env
        fi
        chown www-data:www-data /var/www/html/.env
        # chmod 644 /var/www/html/.env
        chmod 664 /var/www/html/.env
        # 3. Jalankan Composer jika vendor tidak ada
        if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
            echo "Entrypoint: Vendor not found, running composer install as www-data..."
            su-exec www-data composer install --no-progress --no-interaction
        fi

        # 4. Jalankan perintah inisialisasi Laravel sebagai www-data
        # Jalankan perintah inisialisasi Laravel
        echo "Entrypoint (App): Running Laravel artisan commands..."
        su-exec www-data php artisan key:generate
        su-exec www-data php artisan migrate --force
        su-exec www-data php artisan config:clear
        su-exec www-data php artisan cache:clear # Sekarang seharusnya berhasil
        su-exec www-data php artisan route:clear
        su-exec www-data php artisan view:clear
        su-exec www-data php artisan optimize
        # su-exec www-data php artisan db:seed --force # Uncomment jika perlu
        echo "Entrypoint (Role: App): Setup complete."
    else
        echo "Entrypoint (Role: App): CRITICAL WARNING - Not running as root..."
    fi
else
    echo "Entrypoint: Container role is not 'app' (is '$CONTAINER_ROLE'). Skipping app setup."
fi


# Jalankan perintah CMD dari Dockerfile (yaitu, php-fpm)
# FPM akan dimulai sebagai root, lalu menurunkan haknya ke www-data sesuai www.conf
echo "Entrypoint: Handing over to CMD (php-fpm)..."
exec "$@"
```

3. ini Dockerfile saya:

```Dockerfile
# Gunakan base image PHP 8.3 dengan FPM yang berbasis Alpine Linux
FROM php:8.3-fpm-alpine AS base_php
# Set variabel lingkungan untuk mencegah prompt interaktif saat instalasi
ENV DEBIAN_FRONTEND=noninteractive
# Set working directory di dalam kontainer
WORKDIR /var/www/html
# Instal dependensi sistem yang dibutuhkan oleh Laravel dan ekstensi PHP
# 'su-exec' adalah alternatif ringan untuk 'gosu'
RUN apk update && apk add --no-cache \
    build-base \
    linux-headers \
    autoconf \
    automake \
    libtool \
    pkgconfig \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    mariadb-client \
    postgresql-client \
    icu-dev \
    imagemagick-dev \
    imagemagick \
    postgresql-dev \
    oniguruma-dev \
    su-exec \
    && rm -rf /var/cache/apk/*

# Konfigurasi dan instal ekstensi PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    intl \
    opcache \
    pdo_pgsql \
    zip \
    pcntl \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Instal Composer secara global
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# Copy file konfigurasi PHP-FPM kustom (sebagai root)
# COPY./docker/php/zz-fpm-pool.conf /usr/local/etc/php-fpm.d/zz-fpm-pool.conf
USER root
COPY ./docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
# Salin skrip entrypoint yang akan kita buat nanti
COPY ./docker/app/entrypoint.sh /usr/local/bin/docker-app-entrypoint
RUN chmod +x /usr/local/bin/docker-app-entrypoint
USER root
# Atur entrypoint untuk kontainer
ENTRYPOINT ["docker-app-entrypoint"]
# Perintah default untuk dijalankan oleh entrypoint
CMD ["php-fpm"]
```

4. Saya mengaktifkan lagi init-db.sh. ini ini-db.sh saya:

```sh
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
```

5. ini .env saya:

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:+c7q2HzUL8PHDuJ70Ma0dGAOlVE/n6uKRb4wVZPvXFI=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Konfigurasi untuk koneksi Laravel
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=myrvm_platform
DB_USERNAME=myrvm_user # <-- User yang dibuat oleh init-db.sh
DB_PASSWORD=dr4gonlistio # <-- Password yang dibuat oleh init-db.sh

# Variabel untuk SUPERUSER PostgreSQL di docker-compose.yml
# Ini berbeda dari kredensial aplikasi di atas
POSTGRES_USER=postgres
POSTGRES_PASSWORD='8?WC6T=6KmMT' # Ganti dengan password superuser yang kuat
# POSTGRES_DB akan menggunakan nilai DB_DATABASE di atas

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadminsecret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=myrvmv2
AWS_ENDPOINT=http://minio:9000
AWS_URL=http://localhost:9000 # Atau URL publik Anda
AWS_USE_PATH_STYLE_ENDPOINT=true

# FILESYSTEM_DISK=minio
# MINIO_ACCESS_KEY_ID=minioadmin # Ganti dengan user Anda
# MINIO_SECRET_ACCESS_KEY=minioadminsecret # Ganti dengan password Anda
# MINIO_DEFAULT_REGION=us-east-1
# MINIO_BUCKET=myrvmv2 # Nama bucket Anda
# MINIO_USE_PATH_STYLE_ENDPOINT=true
# # Endpoint untuk Laravel berkomunikasi dengan API MinIO (internal Docker)
# MINIO_ENDPOINT=http://minio:9000
# # URL publik dari mana file akan disajikan (via reverse proxy)
# MINIO_URL=http://localhost:9000 # Ganti dengan URL publik Anda nanti, misal https://s3.file.penelitian.my.id

VITE_APP_NAME="${APP_NAME}"

REVERB_APP_ID=981910
REVERB_APP_KEY=f7dg1ppc1uvphgkdwnal
REVERB_APP_SECRET=n5ouwiv7ktywkhzhki0d
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

6. Saya cek isi basis data:

```bash
docker exec -it myrvm_db_dev bash
root@e22178bc07e1:/# psql -U myrvm_user
psql: error: connection to server on socket "/var/run/postgresql/.s.PGSQL.5432" failed: FATAL:  database "myrvm_user" does not exist
root@e22178bc07e1:/# psql -U postgres
psql (17.6 (Debian 17.6-1.pgdg13+1))
Type "help" for help.

postgres=# \l

                                                       List of databases
      Name      |  Owner   | Encoding | Locale Provider |  Collate   |   Ctype    | Locale | ICU Rules |   Access privileges

----------------+----------+----------+-----------------+------------+------------+--------+-----------+-----------------------
 myrvm_platform | postgres | UTF8     | libc            | en_US.utf8 | en_US.utf8 |        |           |
 postgres       | postgres | UTF8     | libc            | en_US.utf8 | en_US.utf8 |        |           |
 template0      | postgres | UTF8     | libc            | en_US.utf8 | en_US.utf8 |        |           | =c/postgres
 +
                |          |          |                 |            |            |        |           | postgres=CTc/postgres
 template1      | postgres | UTF8     | libc            | en_US.utf8 | en_US.utf8 |        |           | =c/postgres
 +
                |          |          |                 |            |            |        |           | postgres=CTc/postgres
(4 rows)
```

7. Kemudian saya debuging myrvm_db_dev:

```bash
docker compose logs -f db
myrvm_db_dev  | The files belonging to this database system will be owned by user "postgres".
myrvm_db_dev  | This user must also own the server process.
myrvm_db_dev  |
myrvm_db_dev  | The database cluster will be initialized with locale "en_US.utf8".
myrvm_db_dev  | The default database encoding has accordingly been set to "UTF8".
myrvm_db_dev  | The default text search configuration will be set to "english".
myrvm_db_dev  |
myrvm_db_dev  | Data page checksums are disabled.
myrvm_db_dev  |
myrvm_db_dev  | fixing permissions on existing directory /var/lib/postgresql/data ... ok
myrvm_db_dev  | creating subdirectories ... ok
myrvm_db_dev  | selecting dynamic shared memory implementation ... posix
myrvm_db_dev  | selecting default "max_connections" ... 100
myrvm_db_dev  | selecting default "shared_buffers" ... 128MB
myrvm_db_dev  | selecting default time zone ... Etc/UTC
myrvm_db_dev  | creating configuration files ... ok
myrvm_db_dev  | running bootstrap script ... ok
myrvm_db_dev  | performing post-bootstrap initialization ... ok
myrvm_db_dev  | syncing data to disk ... ok
myrvm_db_dev  |
myrvm_db_dev  |
myrvm_db_dev  | Success. You can now start the database server using:
myrvm_db_dev  |
myrvm_db_dev  |     pg_ctl -D /var/lib/postgresql/data -l logfile start
myrvm_db_dev  |
myrvm_db_dev  | initdb: warning: enabling "trust" authentication for local connections
myrvm_db_dev  | initdb: hint: You can change this by editing pg_hba.conf or using the option -A, or --auth-local and --auth-host, the next time you run initdb.
myrvm_db_dev  | waiting for server to start....2025-09-06 05:31:35.454 UTC [48] LOG:  starting PostgreSQL 17.6 (Debian 17.6-1.pgdg13+1) on x86_64-pc-linux-gnu, compiled by gcc (Debian 14.2.0-19) 14.2.0, 64-bit
myrvm_db_dev  | 2025-09-06 05:31:35.455 UTC [48] LOG:  listening on Unix socket "/var/run/postgresql/.s.PGSQL.5432"
myrvm_db_dev  | 2025-09-06 05:31:35.460 UTC [51] LOG:  database system was shut down at 2025-09-06 05:31:35 UTC
myrvm_db_dev  | 2025-09-06 05:31:35.465 UTC [48] LOG:  database system is ready to accept connections
myrvm_db_dev  |  done
myrvm_db_dev  | server started
myrvm_db_dev  | CREATE DATABASE
myrvm_db_dev  |
myrvm_db_dev  |
myrvm_db_dev  | /usr/local/bin/docker-entrypoint.sh: running /docker-entrypoint-initdb.d/init-db.sh
myrvm_db_dev  | >>> Running PostgreSQL init script: init-db.sh
myrvm_db_dev  | DO
myrvm_db_dev  | ALTER ROLE
myrvm_db_dev  | ALTER ROLE
myrvm_db_dev  | >>> PostgreSQL init script finished successfully.
myrvm_db_dev  |
myrvm_db_dev  | waiting for server to shut down...2025-09-06 05:31:35.736 UTC [48] LOG:  received fast shutdown request
myrvm_db_dev  | .2025-09-06 05:31:35.737 UTC [48] LOG:  aborting any active transactions
myrvm_db_dev  | 2025-09-06 05:31:35.738 UTC [48] LOG:  background worker "logical replication launcher" (PID 54) exited with exit code 1
myrvm_db_dev  | 2025-09-06 05:31:35.738 UTC [49] LOG:  shutting down
myrvm_db_dev  | 2025-09-06 05:31:35.740 UTC [49] LOG:  checkpoint starting: shutdown immediate
myrvm_db_dev  | 2025-09-06 05:31:35.782 UTC [49] LOG:  checkpoint complete: wrote 929 buffers (5.7%); 0 WAL file(s) added, 0 removed, 0 recycled; write=0.012 s, sync=0.027 s, total=0.044 s; sync files=305, longest=0.002 s, average=0.001 s; distance=4260 kB, estimate=4260 kB; lsn=0/19168B8, redo lsn=0/19168B8
myrvm_db_dev  | 2025-09-06 05:31:35.788 UTC [48] LOG:  database system is shut down
myrvm_db_dev  |  done
myrvm_db_dev  | server stopped
myrvm_db_dev  |
myrvm_db_dev  | PostgreSQL init process complete; ready for start up.
myrvm_db_dev  |
myrvm_db_dev  | 2025-09-06 05:31:35.853 UTC [1] LOG:  starting PostgreSQL 17.6 (Debian 17.6-1.pgdg13+1) on x86_64-pc-linux-gnu, compiled by gcc (Debian 14.2.0-19) 14.2.0, 64-bit
myrvm_db_dev  | 2025-09-06 05:31:35.853 UTC [1] LOG:  listening on IPv4 address "0.0.0.0", port 5432
myrvm_db_dev  | 2025-09-06 05:31:35.853 UTC [1] LOG:  listening on IPv6 address "::", port 5432
myrvm_db_dev  | 2025-09-06 05:31:35.856 UTC [1] LOG:  listening on Unix socket "/var/run/postgresql/.s.PGSQL.5432"
myrvm_db_dev  | 2025-09-06 05:31:35.861 UTC [72] LOG:  database system was shut down at 2025-09-06 05:31:35 UTC
myrvm_db_dev  | 2025-09-06 05:31:35.865 UTC [1] LOG:  database system is ready to accept connections
myrvm_db_dev  | 2025-09-06 05:31:38.451 UTC [78] ERROR:  relation "cache" does not exist at character 15
myrvm_db_dev  | 2025-09-06 05:31:38.451 UTC [78] STATEMENT:  select * from "cache" where "key" in ($1)
myrvm_db_dev  | 2025-09-06 05:31:42.853 UTC [79] ERROR:  permission denied for schema public at character 14
myrvm_db_dev  | 2025-09-06 05:31:42.853 UTC [79] STATEMENT:  create table "migrations" ("id" serial not null primary key, "migration" varchar(255) not null, "batch" integer not null)
```
