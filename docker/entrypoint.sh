#!/bin/bash
set -e

# Pastikan kepemilikan folder storage & bootstrap/cache selalu milik www-data
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Pastikan direktori bukti upload QRIS dan Struk tersedia
mkdir -p /var/www/storage/app/public/payment_proofs /var/www/storage/app/public/receipt_proofs 2>/dev/null || true
chown -R www-data:www-data /var/www/storage/app/public 2>/dev/null || true

# Pasang composer dependencies jika folder vendor belum ada di volume
if [ ! -f /var/www/vendor/autoload.php ]; then
    echo "Folder vendor belum ditemukan. Menjalankan composer install otomatis..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Buat symbolic link storage jika belum ada
if [ ! -L /var/www/public/storage ]; then
    echo "Membuat symbolic link storage..."
    php artisan storage:link || true
fi

# Tunggu database MySQL siap jika DB_HOST didefinisikan (maksimal 15x percobaan / 30 detik)
if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Menunggu koneksi MySQL di $DB_HOST:${DB_PORT:-3306}..."
    RETRY=0
    until mysqladmin ping -h"$DB_HOST" -P"${DB_PORT:-3306}" --silent >/dev/null 2>&1 || [ $RETRY -ge 15 ]; do
        sleep 2
        RETRY=$((RETRY + 1))
    done
    echo "Layanan MySQL siap atau batas tunggu tercapai, melanjutkan proses..."
fi

exec "$@"
