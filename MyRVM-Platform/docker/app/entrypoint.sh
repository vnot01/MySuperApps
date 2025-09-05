#!/bin/sh
set -e

echo "Entrypoint: Script started. Running as root."

# 1. Atur kepemilikan dan permission
echo "Entrypoint: Setting permissions for storage and bootstrap/cache..."
# Buat direktori jika belum ada
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
# Atur kepemilikan
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
# Atur permission
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 2. Tangani file .env
if [ ! -f "/var/www/html/.env" ]; then
    echo "Entrypoint: .env not found, copying from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi
chown www-data:www-data /var/www/html/.env
chmod 664 /var/www/html/.env

# 3. Jalankan Composer jika vendor tidak ada
if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "Entrypoint: Vendor not found, running composer install as www-data..."
    su-exec www-data composer install --no-progress --no-interaction
fi

# 4. Jalankan perintah inisialisasi Laravel sebagai www-data
echo "Entrypoint: Running Laravel artisan commands as www-data..."
su-exec www-data php artisan key:generate
su-exec www-data php artisan migrate --force
# su-exec www-data php artisan db:seed --force # Uncomment jika perlu

echo "Entrypoint: Handing over to CMD (php-fpm)..."
# Jalankan perintah CMD dari Dockerfile (yaitu, php-fpm)
# FPM akan dimulai sebagai root, lalu menurunkan haknya ke www-data sesuai www.conf
exec "$@"