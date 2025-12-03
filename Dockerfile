# Gunakan PHP-FPM resmi versi 8.2
FROM php:8.2-fpm

# Install ekstensi mysqli untuk koneksi MySQL
RUN docker-php-ext-install mysqli

# Set working directory di container
WORKDIR /var/www/html
