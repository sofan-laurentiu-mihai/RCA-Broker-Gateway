# ETAPA 1: Frontend (Tailwind / Vite)
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# ETAPA 2: Laravel pe PHP 8.4
FROM php:8.4-cli

# Dependințe de sistem și extensii PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    libzip-dev \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite zip bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Copiere fișiere statice din frontend
COPY --from=frontend /app/public/build ./public/build

# Fișier .env provizoriu
RUN cp -n .env.example .env || true

# Instalare pachete PHP compatibile
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction

# Pregătire SQLite
RUN touch database/database.sqlite

# Lansare pe portul Render
CMD sh -c "php artisan config:clear && php artisan migrate --force && php artisan serve --host 0.0.0.0 --port \${PORT:-8000}"
