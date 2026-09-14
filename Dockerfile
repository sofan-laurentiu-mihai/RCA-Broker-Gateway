FROM php:8.2-cli

# Instalare dependințe de sistem și extensii PHP
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip nodejs npm sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite mbstring zip bcmath

# Instalare Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Instalare dependințe PHP și JS + build Tailwind
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction
RUN npm install && npm run build

# Creare fișier bază de date SQLite
RUN touch database/database.sqlite

EXPOSE 8000

CMD php artisan migrate --force && php artisan serve --host 0.0.0.0 --port 8000
