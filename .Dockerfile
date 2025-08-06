# Stage 1: PHP + Composer + Node + Build tools
FROM php:8.2-cli AS build

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip \
    nodejs npm

# Install Composer manually
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# Copy source
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Stage 2: Production container with Apache
FROM php:8.2-apache

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev libzip-dev unzip git \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

# Copy built app
COPY --from=build /app /var/www/html

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
