#!/bin/bash
set -e

echo "⏳ Esperando que MySQL esté listo..."
until php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch (Exception \$e) { echo '.'; sleep(2); }"; do :; done

echo -e "\n✅ MySQL disponible. Ejecutando migraciones..."
php artisan migrate --force || true

echo "🧹 Limpiando caché de Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "🚀 Iniciando servidor Laravel..."
exec php artisan serve --host=0.0.0.0 --port=8000
