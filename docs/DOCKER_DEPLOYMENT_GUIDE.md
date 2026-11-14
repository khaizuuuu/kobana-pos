# 🐳 Panduan Lengkap Deploy VPS dengan Docker (Nginx Alpine & MySQL 8.0)
## Kobana POS & Market Basket Analysis
### Kobana Fried Chicken

---

## 1. Arsitektur Kontainer Docker

Sistem dideploy menggunakan arsitektur multi-kontainer (*micro-services stack*) berbasis Docker Compose:

```
                  ┌─────────────────────────────────────────┐
                  │          Internet Publik / Klien        │
                  └────────────────────┬────────────────────┘
                                       │ Port 80 / 443 (HTTP/HTTPS)
                                       ▼
                  ┌─────────────────────────────────────────┐
                  │    kobana-nginx (nginx:alpine)          │
                  │  - Reverse Proxy & SSL Termination      │
                  │  - Static Asset Caching (30d)           │
                  │  - Gzip Compression & Security Headers  │
                  │  - Client Max Body Size: 50MB           │
                  └──────────────┬──────────────────────────┘
                                 │ FastCGI (Port 9000)
                                 ▼
                  ┌─────────────────────────────────────────┐
                  │    kobana-app (PHP 8.3-FPM Alpine)      │
                  │  - Laravel 13 Core                      │
                  │  - OPcache Enabled & Custom php.ini     │
                  │  - Extensions: pdo_mysql, gd, zip, etc. │
                  └───────┬─────────────────────┬───────────┘
                          │                     │
           Koneksi MySQL  │                     │  Volume Persisten
           Port 3306      ▼                     ▼  (storage_data)
┌─────────────────────────────────┐   ┌──────────────────────────────────┐
│   kobana-db (MySQL 8.0)         │   │ /var/www/storage/app/public/     │
│ - Charset: utf8mb4              │   │ - payment_proofs/ (Bukti QRIS)   │
│ - Buffer Pool: 256MB            │   │ - receipt_proofs/ (Struk Belanja)│
│ - Volume: db_data               │   └──────────────────────────────────┘
└─────────────────────────────────┘
        ▲                     ▲
        │                     │
┌───────┴─────────────────────┴───┐   ┌──────────────────────────────────┐
│   kobana-queue (Queue Worker)   │   │   kobana-scheduler (Cron Job)    │
│ - Pemrosesan FP-Growth          │   │ - php artisan schedule:run       │
│ - Impor Dataset Excel Massal    │   │   (Setiap 60 detik)              │
└─────────────────────────────────┘   └──────────────────────────────────┘
```

---

## 2. Persiapan Awal Server VPS

Panduan ini menggunakan sistem operasi **Ubuntu 22.04 LTS / 24.04 LTS** atau **Debian 12**.

### 2.1 Update Sistem Operasi
Hubungkan ke VPS melalui terminal SSH:
```bash
ssh root@IP_VPS_ANDA
apt update && apt upgrade -y
```

### 2.2 Install Docker Engine & Docker Compose Plugin
```bash
# Hapus paket docker lama (jika ada)
apt remove -y docker docker-engine docker.io containerd runc

# Pasang dependensi prasyarat
apt install -y ca-certificates curl gnupg lsb-release ufw git

# Tambahkan GPG key resmi Docker
mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# Tambahkan repositori Docker
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker CE & Docker Compose
apt update
apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Verifikasi instalasi
docker --version
docker compose version
```

### 2.3 Konfigurasi Firewall (UFW)
Amankan VPS Anda dengan hanya membuka port SSH, HTTP, dan HTTPS:
```bash
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp      # Port SSH (pastikan tidak terkunci)
ufw allow 80/tcp      # HTTP Web Server
ufw allow 443/tcp     # HTTPS SSL Web Server
ufw enable
ufw status
```

---

## 3. Langkah-Langkah Deployment

### 3.1 Clone Repositori Proyek
```bash
mkdir -p /var/www
cd /var/www
git clone https://github.com/khaizuuuu/kobana-pos.git kobana-pos
cd kobana-pos
```

### 3.2 Siapkan File Konfigurasi `.env`
Salin template konfigurasi produksi Docker:
```bash
cp .env.production.example .env
nano .env
```
Sesuaikan parameter berikut:
```dotenv
APP_NAME="Kobana POS"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://IP_VPS_ANDA   # atau https://domain-anda.com

# Ganti kata sandi database dengan yang kuat & unik!
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=kobana_pos
DB_USERNAME=kobana_user
DB_PASSWORD=KetikkanPasswordUserDisini_123!
DB_ROOT_PASSWORD=KetikkanPasswordRootDisini_456!
```
*Simpan dengan menekan `CTRL + O`, `Enter`, lalu `CTRL + X`.*

### 3.3 Build & Jalankan Kontainer Docker
Jalankan seluruh stack kontainer di latar belakang:
```bash
docker compose up -d --build
```
*Sistem akan mengunduh image `nginx:alpine` dan `mysql:8.0`, lalu membangun image PHP 8.3 FPM Alpine lengkap dengan ekstensi `pdo_mysql`, `gd`, `zip`, `opcache`, dll.*

Periksa status seluruh kontainer:
```bash
docker compose ps
```
Pastikan seluruh layanan (`kobana-nginx`, `kobana-app`, `kobana-db`, `kobana-queue`, `kobana-scheduler`) berstatus **Up (healthy)**.

### 3.4 Inisialisasi Aplikasi Laravel
Jalankan perintah berikut di dalam kontainer `app`:

```bash
# 1. Pasang dependensi PHP produksi (jika vendor belum terpasang)
docker compose exec app composer install --no-dev --optimize-autoloader

# 2. Hasilkan Application Key
docker compose exec app php artisan key:generate

# 3. Jalankan Migrasi Database MySQL
docker compose exec app php artisan migrate --force

# 4. Jalankan Seeder Akun Bawaan (Admin, Kasir 3 Cabang, Owner, & Dummy Data)
docker compose exec app php artisan db:seed --force

# 5. Hubungkan Symbolic Link Storage Foto Bukti QRIS & Struk
docker compose exec app php artisan storage:link

# 6. Optimasi Cache Produksi
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

Aplikasi Kobana POS sekarang sudah aktif dan dapat diakses melalui browser di:
`http://IP_VPS_ANDA`

---

## 4. Konfigurasi Domain & HTTPS / SSL Gratis (Certbot Let's Encrypt)

Jika Anda sudah menghubungkan Domain (misal `pos.kobana.id`) ke IP VPS Anda:

1. Install Certbot di VPS:
   ```bash
   apt install -y certbot python3-certbot-nginx
   ```
2. Buat sertifikat SSL:
   ```bash
   certbot certonly --standalone -d pos.kobana.id
   ```
3. Pasang sertifikat SSL pada Nginx Docker atau gunakan **Nginx Proxy Manager** / **Cloudflare Full SSL** untuk kemudahan pembaruan otomatis tanpa mematikan kontainer.

---

## 5. Pemeliharaan & Operasional Rutin (*Maintenance*)

### 5.1 Melihat Log Sistem
```bash
# Log seluruh kontainer secara real-time
docker compose logs -f

# Log khusus web server Nginx
docker compose logs -f nginx

# Log pekerjaan antrean FP-Growth & Impor Excel
docker compose logs -f queue

# Log error aplikasi Laravel
docker compose exec app tail -f storage/logs/laravel.log
```

### 5.2 Backup Database Otomatis (Dump MySQL)
Untuk membuat salinan cadangan (*backup*) basis data secara berkala:
```bash
mkdir -p /root/backups
docker compose exec db mysqldump -u kobana_user -pKetikkanPasswordUserDisini_123! kobana_pos | gzip > /root/backups/backup_kobana_$(date +\%Y\%m\%d_\%H\%M\%S).sql.gz
```

### 5.3 Pembaruan Kode Aplikasi (*Update / Git Pull*)
Jika ada pembaruan kode di GitHub:
```bash
cd /var/www/kobana-pos
git pull origin main
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose restart queue scheduler
```
Semua data database (`db_data`) dan foto bukti transaksi QRIS (`storage_data`) tetap **100% aman dan tidak terhapus** karena tersimpan di Volume Persisten Docker.
