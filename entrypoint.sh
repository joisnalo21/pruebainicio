#!/bin/sh
set -e

echo "⌛ Esperando a que MySQL acepte conexiones..."
until php -r "
try {
  \$pdo = new PDO(
    'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD')
  );
  exit(0);
} catch (Exception \$e) {
  exit(1);
}
" >/dev/null 2>&1; do
  sleep 2
done

echo "✅ MySQL listo. Ejecutando tareas Laravel..."

php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan migrate --force

echo "🚀 Iniciando Apache..."
exec "$@"
