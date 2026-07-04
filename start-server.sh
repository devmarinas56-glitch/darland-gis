#!/bin/bash

echo "=== Starting DarLand - Land GIS System ==="

# Clear all caches aggressively
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/views/*.php
rm -rf storage/framework/cache/data/*

# Full permissions
chmod -R 777 storage bootstrap/cache

# Always generate key fresh
php artisan key:generate --force --no-interaction

# Show env for debugging
echo "APP_ENV: $APP_ENV"
echo "DB_CONNECTION: $DB_CONNECTION"
echo "SESSION_DRIVER: $SESSION_DRIVER"

# Clear again after key generation
php artisan config:clear --no-interaction 2>/dev/null || true
php artisan cache:clear --no-interaction 2>/dev/null || true
php artisan view:clear --no-interaction 2>/dev/null || true

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Seed users
echo "Seeding users..."
php artisan db:seed --class=TestUserSeeder --force

echo "Starting on 0.0.0.0:$PORT"
php -S 0.0.0.0:$PORT -t public
