FROM php:8.2-cli

# Instalare dependințe de sistem și extensii PHP necesare pentru Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    sqlite3 \
    libsqlite3-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_sqlite mbstring zip bcmath curl xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalare Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Copiem .env.example ca .env dacă nu există
RUN cp -n .env.example .env || true

# Rulare composer ignorând verificările rigide de versiune de platformă din lockfile
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction --ignore-platform-reqs

# Build pentru asset-urile frontend (Tailwind / Vite)
RUN npm install && npm run build

# Pregătire fișier bază de date SQLite
RUN touch database/database.sqlite

EXPOSE 8000

# La pornirea containerului se pregătesc configurările și se lansează serverul
CMD php artisan key:generate --force && \
    php artisan migrate --force && \
    php artisan serve --host 0.0.0.0 --port 8000
