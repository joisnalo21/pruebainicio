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

# Habilitar mod_rewrite
RUN a2enmod rewrite

# (Recomendado en Laravel + Apache) DocumentRoot a /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Cache de dependencias
COPY composer.json composer.lock ./

# ✅ CLAVE: sin scripts, porque todavía no existe artisan en la imagen
RUN composer install --no-interaction --prefer-dist --no-progress --no-dev --optimize-autoloader --no-scripts

# Copia el resto del proyecto
COPY . .

# Evitar que se copien caches generados en CI (pueden traer providers de --dev como Dusk)
RUN rm -f bootstrap/cache/*.php || true

# Ahora sí
RUN php artisan package:discover --ansi



# Ahora sí, ya existe artisan
RUN php artisan package:discover --ansi

# Permisos Laravel
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 775 storage bootstrap/cache

# Entrypoint
COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Apache normalmente expone 80 (si mapeas 8000:80 en docker-compose, ok)
EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
