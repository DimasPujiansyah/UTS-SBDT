#!/bin/bash
set -e

echo "Menyiapkan container aplikasi Laravel..."

# 1. Salin .env.example -> .env jika belum ada
if [ ! -f /var/www/html/.env ]; then
  echo "File .env belum ada, menyalin dari .env.example..."
  cp /var/www/html/.env.example /var/www/html/.env
fi

# 2. Tunggu database siap
DB_HOST=$(grep -m1 ^DB_HOST= /var/www/html/.env | cut -d '=' -f2)
DB_PORT=$(grep -m1 ^DB_PORT= /var/www/html/.env | cut -d '=' -f2)
DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}

echo "Menunggu database di $DB_HOST:$DB_PORT..."
RETRIES=30
until nc -z "$DB_HOST" "$DB_PORT"; do
  if [ "$RETRIES" -le 0 ]; then
    echo "Timeout menunggu database." >&2
    exit 1
  fi
  sleep 2
  RETRIES=$((RETRIES - 1))
done
echo "Database siap."

# 3. Install dependency composer jika belum ada
if [ ! -d /var/www/html/vendor ]; then
  echo "Menginstall dependency composer..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# 4. Generate application key jika belum ada
if ! grep -q "^APP_KEY=base64" /var/www/html/.env; then
  echo "Membuat application key..."
  php artisan key:generate --force
fi

# 5. Set permission folder storage & cache
mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 6. Jalankan migrasi database
echo "Menjalankan migrasi database..."
php artisan migrate --force

# 7. Buat symbolic link storage
php artisan storage:link || true

# 8. Jalankan cron (Laravel scheduler)
service cron start

echo "Setup selesai, aplikasi siap digunakan."

exec "$@"
