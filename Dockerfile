FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip mbstring xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN cp .env.example .env

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

EXPOSE 8080

RUN chmod +x start-server.sh

# Force rebuild: 2026-09-03-neon
CMD ["./start-server.sh"]
