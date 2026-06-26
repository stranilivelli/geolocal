FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev \
    libzip-dev zip unzip libicu-dev \
    && docker-php-ext-install \
    pdo pdo_mysql mbstring xml \
    zip fileinfo intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN php artisan filament:upgrade

EXPOSE 8080

CMD sh -c "php artisan config:cache && php artisan route:cache && php artisan migrate --force && php -S 0.0.0.0:$PORT -t public"