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
    pdo_pgsql pgsql \
    zip \
    pcntl \
    && pecl install imagick redis \
    && docker-php-ext-enable imagick redis
# Instal ekstensi tambahan jika diperlukan
# RUN pecl install xdebug && docker-php-ext-enable xdebug

# Instal Composer secara global
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy file konfigurasi PHP-FPM kustom (sebagai root)
# COPY./docker/php/zz-fpm-pool.conf /usr/local/etc/php-fpm.d/zz-fpm-pool.conf

USER root
COPY ./docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# COPY entrypoint.sh /usr/local/bin/entrypoint.sh
# RUN chmod +x /usr/local/bin/entrypoint.sh
# ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
# CMD ["php-fpm"] # Or other default command

# Salin skrip entrypoint yang akan kita buat nanti
COPY ./docker/app/entrypoint.sh /usr/local/bin/docker-app-entrypoint
RUN chmod +x /usr/local/bin/docker-app-entrypoint

USER root
# Atur entrypoint untuk kontainer
ENTRYPOINT ["docker-app-entrypoint"]
# Perintah default untuk dijalankan oleh entrypoint
CMD ["php-fpm"]