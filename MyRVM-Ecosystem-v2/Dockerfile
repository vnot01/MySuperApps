FROM php:8.3-fpm-alpine

# Install system dependencies
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
    git \
    libxml2-dev \
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
    gcc \
    g++ \
    make \
    # Python dependencies for Computer Vision Playground V2
    python3 \
    py3-pip \
    py3-numpy \
    py3-opencv \
    py3-pillow \
    py3-matplotlib \
    py3-requests \
    py3-yaml \
    py3-scikit-learn \
    py3-tqdm \
    libgomp \
    libwebp-dev \
    openblas-dev \
    lapack-dev \
    gfortran \
    && rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-install pdo mbstring exif pcntl bcmath gd zip \
    intl \
    opcache \
    pdo_pgsql pgsql \
    && pecl install imagick redis \
    && docker-php-ext-enable imagick redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js and npm
RUN apk add --no-cache nodejs npm

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
