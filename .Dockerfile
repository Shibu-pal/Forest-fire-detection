# Stage 1: Composer and Node build
FROM composer:2.6 AS build

WORKDIR /app

# Install Laravel and dependencies
COPY . .
RUN apt-get update && apt-get install -y unzip git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip nodejs npm
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Stage 2: Serve with PHP and Apache
FROM php:8.2-apache

WORKDIR /var/www/html

# Install PHP extensions
RUN apt-get update && apt-get install -y libpng-dev libonig-dev libxml2-dev zip unzip git \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy built app
COPY --from=build /app /var/www/html

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port
EXPOSE 80
