FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    git \
    unzip \
    zip \
    sqlite3 \
    libsqlite3-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-install pdo pdo_sqlite gd

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Node.js & npm
RUN apt-get install -y nodejs npm

# Copy full Laravel project (root folder)
COPY . /var/www/html/

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader
RUN php artisan storage:link || true
RUN php artisan migrate

# Install Python dependencies
RUN pip3 install --no-cache-dir --break-system-packages -r backend/requirement.txt

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN mkdir -p storage/framework/{cache,sessions,views} \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache storage/framework

# Expose port
EXPOSE 8000

# Start Laravel server
CMD php artisan serve --host=0.0.0.0 --port=8000
