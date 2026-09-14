# ETAPA 1: Construirea frontend-ului (Tailwind / Vite)
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# ETAPA 2: Mediul PHP / Laravel
FROM php:8.2-cli

# Instalare pachete de sistem de bază și extensii PHP
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

# Copiere asset-urile deja compilate din prima etapă
COPY --from=frontend /app/public/build ./public/build

# Configurare fișier temporar .env
RUN cp -n .env.example .env || true

# Rulare composer fără verificări stricte de platformă
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --ignore-platform-reqs

# Pregătire SQLite
RUN touch database/database.sqlite

EXPOSE 8000

CMD php artisan key:generate --force && \
    php artisan migrate --force && \
    php artisan serve --host 0.0.0.0 --port 8000
