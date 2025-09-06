#!/bin/sh
set -e

# Cek variabel environment untuk menentukan peran kontainer
# Kita akan set CONTAINER_ROLE=app di docker-compose.yml untuk service app
if [ "$CONTAINER_ROLE" = "app" ]; then
    # ----------------------------------------------
    # --- TAMBAHKAN BLOK 'WAIT FOR POSTGRES' INI ---
    # ----------------------------------------------
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
    echo "Entrypoint: Waiting for database "$DB_DATABASE" to be created..."
    # Kita menggunakan psql untuk mencoba listing database sampai berhasil
    # Variabel PG* diambil dari .env Laravel yang di-mount
    export PGPASSWORD=$DB_PASSWORD  
    until psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -c '\q'; do
      >&2 echo "Postgres is unavailable (user: $DB_USERNAME) - sleeping"
      sleep 2
    done
    >&2 echo "Postgres is up and database is ready - executing command"
    
    # Grant all privileges on the 'public' schema to your application user
    # Replace 'your_db_user' and 'your_schema_name' with your actual values
    # psql -h "$DB_HOST" -p "$DB_PORT" -U "$POSTGRES_USERNAME" -d "$DB_DATABASE" -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "$DB_USERNAME";"
    # psql -h "$DB_HOST" -p "$DB_PORT" -U "$POSTGRES_USERNAME" -d "$DB_DATABASE" -c "GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "$DB_USERNAME";"
    # psql -h "$DB_HOST" -p "$DB_PORT" -U "$POSTGRES_USERNAME" -d "$DB_DATABASE" -c "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL PRIVILEGES ON TABLES TO "$DB_USERNAME";"
    # psql -h "$DB_HOST" -p "$DB_PORT" -U "$POSTGRES_USERNAME" -d "$DB_DATABASE" -c "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL PRIVILEGES ON SEQUENCES TO "$DB_USERNAME";"
    echo "Entrypoint: Database "$DB_DATABASE" is ready."
    # echo "Entrypoint: Container role (all privileges) is "$CONTAINER_ROLE". Running full setup..."

    # --------------------------------------
    # --- AKHIR BLOK 'WAIT FOR POSTGRES' ---
    # --------------------------------------

    # Sekarang kita 100% yakin database dan user sudah siap
    # Langkah 3: Jalankan setup aplikasi Laravel
    if [ "$(id -u)" = '0' ]; then
        echo "Entrypoint (Role: App): Running as root, setting up environment..."
        # ... (semua logika chown, mkdir, artisan migrate, key:generate, dll. masuk ke sini) ...
        echo "############## Bagian setup aplikasi Laravel ##############"
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
        # chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
        # chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache
        chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache # Gunakan ini hanya jika 775 tidak cukup
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
        # Generate application key if not set
        if [ ! -f .env ]; then
            cp .env.example .env
        fi

        echo "--Entrypoint (App): Generate application key if not set--"
        # Generate application key if not set
        
        if [ -z "$APP_KEY" ] && [ "$CONTAINER_ROLE" = "app" ]; then
            if grep -q "APP_KEY=$" .env; then
                echo "Generating application key..."                
                php artisan key:generate --force
            else
                echo "APP_KEY already set in .env, skipping key generation."
            fi
        fi
        # su-exec www-data php artisan key:generate
        # Run migrations and seed if the database is 
        echo "--Entrypoint (App): Run migrations and seed if the database is--"
        if [ -f "/var/www/html/artisan" ] && [ "$CONTAINER_ROLE" = "app" ]; then
            echo "Running migrations and seeding..."
            php artisan migrate --force
            # php artisan db:seed --force
        fi
        # su-exec www-data php artisan migrate --force
        # Optimize Laravel
        echo "--Entrypoint (App): Optimize Laravel--"
        if [ "$CONTAINER_ROLE" = "app" ]; then
            echo "Optimizing Laravel..."
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
        fi
        # su-exec www-data php artisan config:clear
        # su-exec www-data php artisan cache:clear # Sekarang seharusnya berhasil
        # su-exec www-data php artisan route:clear
        # su-exec www-data php artisan view:clear
        # su-exec www-data php artisan optimize
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