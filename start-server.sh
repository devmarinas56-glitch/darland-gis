#!/bin/bash

echo "=== Starting DarLand - Land GIS System ==="

# Clear caches
rm -rf bootstrap/cache/*.php
php artisan cache:clear --no-interaction 2>/dev/null || true
php artisan config:clear --no-interaction 2>/dev/null || true
php artisan route:clear --no-interaction 2>/dev/null || true
php artisan view:clear --no-interaction 2>/dev/null || true

# Generate app key
php artisan key:generate --force --no-interaction

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Seed test users
echo "Seeding users..."
php artisan db:seed --class=TestUserSeeder --force

# Create storage symlink
php artisan storage:link 2>/dev/null || true

# Set APP_URL
if [ -z "$APP_URL" ]; then
    export APP_URL="https://darland.onrender.com"
fi

echo "Starting server on 0.0.0.0:$PORT"
php -S 0.0.0.0:$PORT -t public
