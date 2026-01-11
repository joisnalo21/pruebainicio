FROM php:8.2-apache-bookworm

# Dependencias del sistema + extensiones PHP
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    pkg-config \
    libonig-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libxml2-dev \
    zip unzip curl git \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mbstring xml \
 && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite (Laravel lo requiere)
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

# Permisos Laravel
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 storage bootstrap/cache

# Entrypoint
COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["/entrypoint.sh"]
