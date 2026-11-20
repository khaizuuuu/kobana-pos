#!/bin/bash
set -e

if [ -z "$1" ]; then
    echo "=========================================================="
    echo " Panduan Penggunaan Setup SSL HTTPS Let's Encrypt"
    echo "=========================================================="
    echo "Format: sudo bash setup-ssl.sh <namadomain.duckdns.org> [email@anda.com]"
    echo "Contoh: sudo bash setup-ssl.sh kobana-pos.duckdns.org"
    echo "=========================================================="
    exit 1
fi

DOMAIN="$1"
EMAIL="$2"

echo "=========================================================="
echo " Memulai Konfigurasi SSL HTTPS untuk: $DOMAIN"
echo "=========================================================="

# 1. Pastikan Certbot terpasang di sistem host
if ! command -v certbot &> /dev/null; then
    echo "Certbot belum terpasang. Memasang certbot..."
    apt-get update && apt-get install -y certbot
fi

# 2. Hentikan sementara kontainer Nginx agar port 80 & 443 bebas untuk validasi Let's Encrypt
echo "Menghentikan sementara kontainer Nginx untuk verifikasi domain..."
docker compose stop nginx 2>/dev/null || true

# 3. Generate Sertifikat SSL Let's Encrypt
echo "Menghubungi Let's Encrypt untuk membuat sertifikat SSL..."
if [ -n "$EMAIL" ]; then
    certbot certonly --standalone -d "$DOMAIN" --non-interactive --agree-tos --email "$EMAIL"
else
    certbot certonly --standalone -d "$DOMAIN" --non-interactive --agree-tos --register-unsafely-without-email
fi

if [ ! -d "/etc/letsencrypt/live/$DOMAIN" ]; then
    echo "Error: Sertifikat SSL gagal dibuat!"
    echo "Pastikan domain $DOMAIN sudah benar-benar diarahkan ke IP VPS ini di DuckDNS."
    docker compose start nginx 2>/dev/null || true
    exit 1
fi

echo "Sertifikat SSL Let's Encrypt berhasil dibuat di /etc/letsencrypt/live/$DOMAIN!"

# 4. Buat konfigurasi Nginx HTTPS otomatis
echo "Mengonfigurasi Nginx untuk HTTPS..."
cat <<'EOF' > docker/nginx/conf.d/default.conf
server {
    listen 80;
    listen [::]:80;
    server_name DOMAIN_PLACEHOLDER;

    # Penanganan verifikasi perpanjangan SSL Certbot
    location /.well-known/acme-challenge/ {
        root /var/www/public;
    }

    # Redirect seluruh lalu lintas HTTP ke HTTPS
    location / {
        return 301 https://$host$request_uri;
    }
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name DOMAIN_PLACEHOLDER;

    # Sertifikat SSL Let's Encrypt
    ssl_certificate /etc/letsencrypt/live/DOMAIN_PLACEHOLDER/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/DOMAIN_PLACEHOLDER/privkey.pem;

    # Pengaturan Keamanan SSL Modern
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 1d;

    root /var/www/public;
    index index.php index.html;
    charset utf-8;

    # Kapasitas unggah berkas (disesuaikan untuk foto QRIS & berkas Excel dataset)
    client_max_body_size 50M;
    client_body_buffer_size 128k;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Kompresi Gzip
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_min_length 256;
    gzip_types
        application/javascript
        application/json
        application/xml
        application/xhtml+xml
        image/svg+xml
        text/css
        text/javascript
        text/plain
        text/xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Penanganan berkas statis
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|webp|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        access_log off;
        add_header Cache-Control "public, no-transform";
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Blokir akses ke berkas tersembunyi (.env, .git, dll)
    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Proxy FastCGI ke kontainer PHP-FPM
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;

        fastcgi_param HTTPS on;
        fastcgi_param HTTP_X_FORWARDED_PROTO https;

        fastcgi_read_timeout 300s;
        fastcgi_send_timeout 300s;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
    }

    error_log /var/log/nginx/kobana_error.log warn;
    access_log /var/log/nginx/kobana_access.log;
}
EOF

# Ganti placeholder dengan nama domain aktual
sed -i "s/DOMAIN_PLACEHOLDER/$DOMAIN/g" docker/nginx/conf.d/default.conf

# 5. Perbarui APP_URL di file .env menjadi https://$DOMAIN
if [ -f .env ]; then
    echo "Memperbarui variabel APP_URL di .env menjadi https://$DOMAIN..."
    sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" .env
fi

# 6. Nyalakan kembali kontainer Nginx & App
echo "Menyalakan kembali kontainer Nginx dengan SSL aktif..."
docker compose up -d nginx app

# 7. Bersihkan cache Laravel agar mengenali HTTPS
echo "Membersihkan cache aplikasi Laravel..."
docker compose exec app php artisan config:clear || true
docker compose exec app php artisan route:clear || true
docker compose exec app php artisan view:clear || true

# 8. Daftarkan Cron Job perpanjangan otomatis sertifikat SSL (Let's Encrypt renew)
echo "Mengonfigurasi auto-renewal SSL di crontab host..."
PROJECT_DIR="$(pwd)"
(crontab -l 2>/dev/null | grep -v "certbot renew"; echo "0 3 * * * certbot renew --quiet --pre-hook 'docker compose -f $PROJECT_DIR/docker-compose.yml stop nginx' --post-hook 'docker compose -f $PROJECT_DIR/docker-compose.yml start nginx'") | crontab -

echo "=========================================================="
echo " SELAMAT! Konfigurasi HTTPS Berhasil!"
echo " Silakan buka di browser Anda:"
echo " 👉 https://$DOMAIN"
echo "=========================================================="
