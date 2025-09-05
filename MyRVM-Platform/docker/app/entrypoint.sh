#!/bin/sh
# Exit immediately if a command exits with a non-zero status.
set -e

# Jalankan chown dan chmod untuk storage dan bootstrap/cache
# Ini akan memastikan Laravel bisa menulis ke direktori-direktori ini
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Jika direktori vendor belum ada, jalankan composer install
if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction
fi

# Jalankan perintah CMD dari Dockerfile (yaitu, php-fpm) sebagai user www-data
exec su-exec www-data "$@"