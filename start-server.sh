#!/bin/bash

echo "=== Starting DarLand - Land GIS System ==="

# Clear all caches
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/views/*.php
php artisan cache:clear --no-interaction 2>/dev/null || true
php artisan config:clear --no-interaction 2>/dev/null || true
php artisan route:clear --no-interaction 2>/dev/null || true
php artisan view:clear --no-interaction 2>/dev/null || true

# Ensure storage directories exist with full permissions
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/logs
chmod -R 777 storage
chmod -R 777 bootstrap/cache

# Generate app key
php artisan key:generate --force --no-interaction

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Seed test users
echo "Seeding users..."
php artisan db:seed --class=TestUserSeeder --force

# Storage link
php artisan storage:link 2>/dev/null || true

# Set APP_URL
if [ -z "$APP_URL" ]; then
    export APP_URL="https://darland-gis.onrender.com"
fi

echo "APP_URL: $APP_URL"
echo "Starting server on 0.0.0.0:$PORT"
php -S 0.0.0.0:$PORT -t public
