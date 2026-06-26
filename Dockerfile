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

RUN mkdir -p public/css/filament/filament \
    public/css/filament/forms \
    public/css/filament/support \
    public/js/filament/filament \
    public/js/filament/forms \
    public/js/filament/notifications \
    public/js/filament/support \
    public/js/filament/tables \
    public/js/filament/widgets && \
    cp -r vendor/filament/filament/dist/. public/css/filament/filament/ 2>/dev/null || true && \
    find vendor/filament -name "*.css" -path "*/dist/*" -exec cp {} public/css/filament/ \; 2>/dev/null || true && \
    find vendor/filament -name "*.js" -path "*/dist/*" -exec cp {} public/js/filament/ \; 2>/dev/null || true

EXPOSE 8080

CMD sh -c "php artisan filament:upgrade && php artisan config:cache && php artisan route:cache && php artisan migrate --force && php -S 0.0.0.0:$PORT -t public"