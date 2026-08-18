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

# Run migrations (fresh to apply schema changes including username column)
echo "Running migrations..."
php artisan migrate:fresh --force

# NOTE: Change migrate:fresh back to migrate after first successful deploy

# Seed users
echo "Seeding users..."
php artisan db:seed --class=TestUserSeeder --force

# Verify seeded passwords (debug)
echo "=== Verifying seeded accounts ==="
php artisan tinker --no-interaction <<'EOF'
$u = \App\Models\User::where('email','admin@darland.com')->first();
if ($u) {
    echo "Admin found. Password hash: " . $u->getRawOriginal('password') . "\n";
    echo "Hash check: " . (password_verify('admin123', $u->getRawOriginal('password')) ? 'PASS' : 'FAIL') . "\n";
} else {
    echo "Admin NOT found in database!\n";
}
EOF

echo "Starting on 0.0.0.0:$PORT"
php -S 0.0.0.0:$PORT -t public
