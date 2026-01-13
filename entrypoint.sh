#!/bin/sh
set -e

# ===== Timezone (Ecuador) =====
export TZ="${TZ:-America/Guayaquil}"

# PHP timezone (recomendado)
mkdir -p /usr/local/etc/php/conf.d 2>/dev/null || true
echo "date.timezone = ${TZ}" > /usr/local/etc/php/conf.d/99-timezone.ini 2>/dev/null || true

echo "🕒 PHP timezone set to: ${TZ}"



echo "⌛ Esperando a que MySQL acepte conexiones..."

# ---- Configs seguros por defecto (por si faltan en env) ----
DB_HOST="${DB_HOST:-mysql}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

# ---- Timeout para no esperar infinito ----
MAX_TRIES="${MAX_TRIES:-45}"
SLEEP_SECONDS="${SLEEP_SECONDS:-2}"

i=1
until php -r "
try {
  \$pdo = new PDO(
    'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD'),
    [PDO::ATTR_TIMEOUT => 2]
  );
  exit(0);
} catch (Exception \$e) {
  exit(1);
}
" >/dev/null 2>&1; do
  if [ "$i" -ge "$MAX_TRIES" ]; then
    echo "❌ MySQL no respondió después de ${MAX_TRIES} intentos."
    exit 1
  fi
  echo "   ... MySQL aún no responde ($i/$MAX_TRIES)"
  i=$((i+1))
  sleep "$SLEEP_SECONDS"
done

echo "✅ MySQL listo. Ejecutando tareas Laravel..."

# ---- Asegurar carpetas necesarias (evita: View path not found / cache issues) ----
mkdir -p \
  resources/views \
  storage/framework/views \
  storage/framework/cache \
  storage/framework/sessions \
  storage/logs \
  bootstrap/cache

# ---- Permisos (en apache/php official image el usuario es www-data) ----
# No fallar si el contenedor no tiene permisos para chown (depende del usuario)
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# ---- Limpieza de caches (no queremos que tumbe el contenedor) ----
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# ---- Migraciones (si falla, sí debe fallar porque es importante) ----
php artisan migrate --force

echo "🚀 Iniciando Apache..."
exec apache2-foreground
