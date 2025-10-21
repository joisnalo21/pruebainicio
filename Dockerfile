FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip git unzip curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Habilitar mod_rewrite para Laravel
RUN a2enmod rewrite

# Copiar el código del proyecto
COPY . /var/www/html

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Puerto expuesto
EXPOSE 8000

# Comando de inicio (servidor Laravel)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
