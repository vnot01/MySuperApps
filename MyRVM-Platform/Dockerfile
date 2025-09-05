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
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Instal Composer secara global
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Salin skrip entrypoint yang akan kita buat nanti
COPY ./docker/app/entrypoint.sh /usr/local/bin/docker-app-entrypoint
RUN chmod +x /usr/local/bin/docker-app-entrypoint

# Atur entrypoint untuk kontainer
ENTRYPOINT ["docker-app-entrypoint"]

# Perintah default untuk dijalankan oleh entrypoint
CMD ["php-fpm"]