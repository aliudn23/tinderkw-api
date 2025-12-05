FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Publish L5-Swagger assets and config
RUN php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider" --force

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/storage/api-docs \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Generate Swagger documentation at build time
RUN php artisan l5-swagger:generate || true

# Create entrypoint script
RUN echo '#!/bin/bash\n\
set -e\n\
echo "Starting TinderKW API..."\n\
\n\
# Wait for database to be ready\n\
echo "Waiting for database..."\n\
until php artisan migrate --force 2>/dev/null; do\n\
  echo "Database is unavailable - sleeping"\n\
  sleep 2\n\
done\n\
\n\
echo "Database is ready!"\n\
\n\
# Run migrations\n\
echo "Running migrations..."\n\
php artisan migrate --force\n\
\n\
# Seed database if not already seeded\n\
echo "Seeding database..."\n\
php artisan db:seed --force || echo "Seeding skipped (may already be seeded)"\n\
\n\

\n\
# Clear all caches first\n\
echo "Clearing caches..."\n\
php artisan config:clear\n\
php artisan route:clear\n\
php artisan cache:clear\n\
php artisan view:clear\n\
\n\
# Create storage link for public access\n\
php artisan storage:link || true\n\
\n\
# Generate Swagger documentation\n\
echo "Generating Swagger documentation..."\n\
php artisan l5-swagger:generate\n\
\n\
# Cache config and views (but NOT routes - breaks L5-Swagger)\n\
echo "Caching configuration..."\n\
php artisan config:cache\n\
php artisan view:cache\n\
\n\
# Fix permissions again\n\
chown -R www-data:www-data /var/www/html/storage\n\
chmod -R 775 /var/www/html/storage\n\
\n\
echo "Starting services..."\n\
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf\n\
' > /entrypoint.sh && chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
