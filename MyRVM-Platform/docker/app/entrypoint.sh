#!/bin/sh
set -e

# Cek variabel environment untuk menentukan peran kontainer
# Kita akan set CONTAINER_ROLE=app di docker-compose.yml untuk service app
if [ "$CONTAINER_ROLE" = "app" ]; then
    echo "Entrypoint: Container role is 'app'. Running full setup..."

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
        echo "Entrypoint: Running Laravel artisan commands..."
        su-exec www-data php artisan key:generate
        su-exec www-data php artisan migrate --force
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