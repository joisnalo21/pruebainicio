FROM php:8.2-apache-bookworm

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

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-progress

COPY . .

RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 storage bootstrap/cache

COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["/entrypoint.sh"]
