FROM php:8.4-apache

# Cài các PHP extensions và Node.js (LTS) cần thiết
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev curl ca-certificates gnupg \
    libpng-dev libjpeg62-turbo-dev \
    libfreetype6-dev libicu-dev \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_22.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update && apt-get install -y nodejs \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql bcmath gd intl zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy source Laravel
COPY . .

# Cài PHP dependencies & build Vite assets ngay lúc build image
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && if [ -f package-lock.json ]; then npm ci; else npm install; fi \
    && npm run build

# Cấu hình Apache trỏ vào public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# Cấp quyền storage, cache và public/build
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["sh", "-c", "php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan storage:link --force && exec apache2-foreground"]