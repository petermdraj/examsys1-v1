# ── Base PHP image with extensions required by Composer + runtime ───────────
FROM php:8.3-fpm-alpine AS php-base

RUN apk add --no-cache \
    bash curl git icu-dev icu-libs libpng-dev libxml2-dev libzip-dev oniguruma-dev \
    freetype-dev libjpeg-turbo-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath dom exif gd intl mbstring opcache pdo_mysql xml zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── Stage 1: PHP dependencies (needed for Tailwind content paths in vendor/) ──
FROM php-base AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# ── Stage 2: build frontend assets ──────────────────────────────────────────
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install --ignore-scripts
COPY vite.config.js tailwind.config.js postcss.config.cjs ./
COPY resources ./resources
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# ── Stage 3: PHP application ────────────────────────────────────────────────
FROM php-base

WORKDIR /var/www/html

COPY composer.json composer.lock ./
COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
