# =========================================================
# STAGE 1: Build frontend assets (Vite + Tailwind)
# =========================================================
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json .npmrc ./
RUN npm install --ignore-scripts

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# =========================================================
# STAGE 2: Install PHP dependencies (Composer)
# =========================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts \
    --no-autoloader

COPY . .
RUN composer dump-autoload --optimize --no-dev

# =========================================================
# STAGE 3: Runtime image (PHP 8.3 + Apache)
# =========================================================
FROM php:8.3-apache

# PHP extensions Laravel cần (pdo_mysql cho MySQL)
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install pdo_mysql bcmath intl zip opcache \
    && rm -rf /var/lib/apt/lists/*

# Trỏ DocumentRoot vào thư mục public/ của Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

WORKDIR /var/www/html

# Copy source code + vendor + assets đã build
COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

# Quyền ghi cho storage và bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
