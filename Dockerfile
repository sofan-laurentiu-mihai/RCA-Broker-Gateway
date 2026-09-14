# ETAPA 1: Construirea frontend-ului (Tailwind / Vite)
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# ETAPA 2: Mediul PHP / Laravel (PHP 8.3)
FROM php:8.3-cli

# Instalare pachete de sistem și extensii PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    libzip-dev \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite zip bcmath \
    && rm -rf /var/lib/apt/lists/*

# Composer oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiere cod sursă
COPY . .

# Copiere asset-urile compilate
COPY --from=frontend /app/public/build ./public/build

# Fișier temporar de mediu
RUN cp -n .env.example .env || true

# Instalare pachete PHP (generează automat și autoloader-ul)
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --ignore-platform-reqs

# Pregătire fișier SQLite
RUN touch database/database.sqlite

# Script de pornire: aplică migrările și pornește pe portul alocat de Render
CMD sh -c "php artisan key:generate --force && php artisan migrate --force && php artisan serve --host 0.0.0.0 --port \${PORT:-8000}"
