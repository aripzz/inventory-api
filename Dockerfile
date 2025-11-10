# ----------------------------------------------------------------------
# STAGE 1: BUILDER (Instalasi Composer Dependencies)
# ----------------------------------------------------------------------
FROM composer:2.7 AS builder

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install application dependencies
# RUN composer install --ignore-platform-reqs --no-dev --prefer-dist --optimize-autoloader
RUN for i in 1 2 3; do composer install --ignore-platform-reqs --no-dev --prefer-dist --optimize-autoloader && break || sleep 10; done

# Copy the rest of the application files
COPY . .

# Run NPM (optional, jika Anda memiliki aset frontend)
# FROM node:20 as npm_builder
# WORKDIR /app
# COPY . .
# RUN npm install
# RUN npm run build


# ----------------------------------------------------------------------
# STAGE 2: PRODUCTION (PHP-FPM Runtime)
# ----------------------------------------------------------------------
FROM php:8.2-fpm-alpine AS production

# Instalasi Ekstensi PHP yang dibutuhkan Laravel
# Tambahkan pdo_mysql untuk koneksi database
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    mariadb-client \
    git \
    && docker-php-ext-install pdo pdo_mysql opcache \
    && docker-php-ext-enable opcache

# Set working directory
WORKDIR /var/www/html

# Copy application files from the builder stage
COPY --from=builder /app /var/www/html

# Ganti ownership ke user www-data
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html

# Set default command untuk menjalankan PHP-FPM
CMD ["php-fpm"]
