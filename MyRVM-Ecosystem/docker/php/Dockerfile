# Gunakan base image PHP 8.3 dengan FPM dan Alpine Linux
FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install dependensi sistem yang dibutuhkan
# Menambahkan libxml2-dev untuk ext-xml dan ext-dom
RUN apk add --no-cache \
    build-base \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    libxml2-dev \ # <-- Ditambahkan untuk xml & dom
    git \
    unzip \
    curl

# Install ekstensi PHP yang umum digunakan Laravel
RUN docker-php-ext-install \
    pdo_pgsql \
    pcntl \
    bcmath \
    gd \
    zip \
    exif \
    soap \ # <-- ext-dom adalah dependensi untuk soap
    xml     # <-- ext-xml

# Install ekstensi Redis
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set user dan group untuk aplikasi
RUN addgroup -g 1000 www && \
    adduser -u 1000 -G www -s /bin/sh -D www

# Ganti user ke non-root
USER www

# Expose port untuk PHP-FPM
EXPOSE 9000

# Jalankan PHP-FPM
CMD ["php-fpm"]