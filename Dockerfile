FROM php:8.2-apache

# Instalar dependencias necesarias para Laravel y extensiones de PHP
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip git unzip curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Habilitar mod_rewrite para Apache (requerido por Laravel)
RUN a2enmod rewrite

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar el código fuente del proyecto
COPY . .

# Ajustar permisos para Laravel (storage y cache)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Copiar y dar permisos al script de entrada
COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Exponer el puerto 8000 (el mismo usado por php artisan serve)
EXPOSE 8000

# Usar el entrypoint para manejar el arranque de Laravel
ENTRYPOINT ["/entrypoint.sh"]

