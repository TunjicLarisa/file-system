FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .

RUN npm run build


FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libsqlite3-dev \
        libonig-dev \
        libzip-dev \
    && docker-php-ext-install \
        pdo_sqlite \
        mbstring \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
    && cp .env.example .env \
    && php artisan key:generate --force \
    && mkdir -p \
        storage/app \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache .env

COPY --from=frontend /app/public/build ./public/build

USER www-data

EXPOSE 8000

CMD ["sh", "-c", "php artisan config:clear && if [ ! -f \"$DB_DATABASE\" ]; then touch \"$DB_DATABASE\" && php artisan migrate --seed --force; else php artisan migrate --force; fi && cd public && exec php -S 0.0.0.0:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"]