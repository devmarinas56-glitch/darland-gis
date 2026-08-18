#!/bin/bash

echo "=== Starting DarLand - Land GIS System ==="

# Clear all caches aggressively
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/views/*.php
rm -rf storage/framework/cache/data/*

# Full permissions
chmod -R 777 storage bootstrap/cache

# Only generate key if not already set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force --no-interaction
fi

# Show env for debugging
echo "APP_ENV: $APP_ENV"
echo "DB_CONNECTION: $DB_CONNECTION"
echo "SESSION_DRIVER: $SESSION_DRIVER"
echo "CACHE_STORE: $CACHE_STORE"

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Seed users
echo "Seeding users..."
php artisan db:seed --class=TestUserSeeder --force

# Seed land lots
echo "Seeding land lots..."
php artisan db:seed --class=LandLotSeeder --force

echo "Starting on 0.0.0.0:$PORT"
php -S 0.0.0.0:$PORT -t public
