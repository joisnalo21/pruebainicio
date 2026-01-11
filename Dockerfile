FROM php:8.2-apache-bookworm

# Dependencias del sistema + extensiones PHP
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    pkg-config \
    libonig-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libxml2-dev \
    libzip-dev zlib1g-dev \
    zip unzip curl git \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mbstring xml zip \
 && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite (si algún día sirves por Apache)
RUN a2enmod rewrite

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Instala deps primero para aprovechar cache
COPY composer.json composer.lock ./

# ✅ Para contenedor de runtime: NO necesitas dusk/selenium, así que mejor sin dev
RUN composer install --no-interaction --prefer-dist --no-progress --no-dev --optimize-autoloader

# Copia el resto del proyecto
COPY . .

# Permisos Laravel
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 775 storage bootstrap/cache

# Entrypoint
COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["/entrypoint.sh"]
