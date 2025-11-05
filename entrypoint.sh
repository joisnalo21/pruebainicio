#!/bin/sh

echo "⌛ Esperando a que MySQL acepte conexiones externas..."
until php -r "
try {
    \$pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    echo '✅ Conexión establecida con MySQL.' . PHP_EOL;
    exit(0);
} catch (Exception \$e) {
    echo '⏳ Aún no disponible: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}" >/dev/null 2>&1; do
    sleep 2
done

# Limpieza de caché y migraciones
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force

# Ejecutar Laravel
exec php artisan serve --host=0.0.0.0 --port=8000
