# ===== 1) Stage de Composer: instala vendor =====
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-progress

COPY . .
RUN composer dump-autoload -o


# ===== 2) Imagen final con Apache =====
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

# Apache debe apuntar a /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copiamos TODO el proyecto ya con vendor incluido
COPY --from=vendor /app /var/www/html

# Permisos Laravel
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 775 storage bootstrap/cache

# Entrypoint
COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
