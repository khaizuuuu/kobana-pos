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

echo "Inisialisasi selesai. Menjalankan layanan..."
exec "$@"
