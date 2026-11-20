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

## 4. Konfigurasi Domain Gratis (DuckDNS) & HTTPS / SSL Let's Encrypt

Anda dapat menggunakan subdomain gratis dari **DuckDNS** (misal: `kobana-pos.duckdns.org`) dan mengaktifkan HTTPS (gembok hijau) secara otomatis:

### 4.1 Langkah di Website DuckDNS:
1. Buka [https://www.duckdns.org](https://www.duckdns.org) dan login (bisa via Google/GitHub).
2. Di bagian **domains**, buat subdomain baru (misal: `kobana-pos`).
3. Pada kolom **current IP**, masukkan IP VPS Anda: `43.157.206.88`.
4. Klik **update ip**. Domain Anda (`kobana-pos.duckdns.org`) kini resmi mengarah ke VPS Anda.

### 4.2 Pasang SSL Otomatis Menggunakan Skrip `setup-ssl.sh`:
Telah disediakan skrip otomatis `setup-ssl.sh` di root proyek. Jalankan di terminal VPS:
```bash
cd /web-apps/kobana
git pull origin main
sudo bash setup-ssl.sh namadomainanda.duckdns.org
```
*Skrip ini akan secara otomatis: memasang Certbot, membuat sertifikat SSL Let's Encrypt gratis, mengatur Nginx HTTPS di port 443 dengan redirect otomatis dari HTTP port 80, memperbarui `APP_URL` di `.env`, membersihkan cache Laravel, dan mendaftarkan cron job auto-renewal SSL.*

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

---

## 6. Panduan Menghubungkan MySQL Workbench Terbaru ke Database VPS Docker

Aplikasi Kobana POS menggunakan MySQL 8.0 di dalam kontainer Docker. Anda dapat mengelola database, menjalankan query analisis, melihat tabel transaksi, maupun melakukan backup/restore menggunakan **MySQL Workbench** versi terbaru melalui 2 metode di bawah ini.

```
┌──────────────────────────────────────────────────────────────┐
│                    MySQL Workbench (PC Anda)                 │
└──────────────┬───────────────────────────────┬───────────────┘
               │                               │
  [Metode 1: SSH Tunnel]                       │ [Metode 2: Direct TCP/IP]
  Port 22 SSH (Encrypted)                      │ Port 3306 (Publik)
  100% Aman & Terisolasi                       │ Membutuhkan UFW Whitelist
               ▼                               ▼
┌───────────────────────────────┐     ┌───────────────────────────────┐
│     VPS SSH Daemon (Port 22)  │     │     VPS Firewall (UFW 3306)   │
└──────────────┬────────────────┘     └──────────────┬────────────────┘
               │                                     │
               ▼ (Forward ke 127.0.0.1:3306)         │
┌────────────────────────────────────────────────────┴────────────────┐
│                   Kontainer Docker: kobana-db (MySQL 8.0)           │
│     - skip-name-resolve = 1 (Koneksi Workbench Instan Tanpa Lag)    │
│     - interactive_timeout = 28800 (Sesi GUI Tidak Pernah Terputus)  │
│     - max_allowed_packet = 64M (Dukungan Ekspor/Impor Skema Besar)  │
└─────────────────────────────────────────────────────────────────────┘
```

---

### 6.1 Metode 1: Standard TCP/IP over SSH (Rekomendasi Utama & Paling Aman)

Metode ini adalah **standar industri terbaik**. Anda **TIDAK PERLU** membuka port 3306 ke internet publik (tetap menggunakan `DB_BIND_IP=127.0.0.1`), sehingga database Anda 100% kebal dari serangan pemindaian bot dan brute-force hacker.

#### Langkah-langkah di MySQL Workbench:
1. Buka aplikasi **MySQL Workbench** di komputer Anda.
2. Pada layar utama, klik tombol **`+`** di sebelah teks **MySQL Connections** untuk membuat koneksi baru.
3. Isi parameter form sebagai berikut:
   - **Connection Name:** `Kobana POS VPS (SSH Tunnel)`
   - **Connection Method:** Pilih dropdown **`Standard TCP/IP over SSH`**
   - **SSH Hostname:** Masukkan `IP_VPS_ANDA:22` *(Contoh: `103.187.145.20:22`)*
   - **SSH Username:** Masukkan user SSH VPS Anda *(Contoh: `root` atau username sudo Anda)*
   - **SSH Password:** Klik *Store in Vault...* lalu ketikkan kata sandi SSH VPS Anda (atau pilih file `.pem` / `.id_rsa` di **SSH Keyfile** jika menggunakan autentikasi SSH Key).
   - **MySQL Hostname:** Ketik **`127.0.0.1`**
   - **MySQL Server Port:** Ketik **`3306`**
   - **Username:** `kobana_user` *(atau `root`)*
   - **Password:** Klik tombol *Store in Vault...*, lalu masukkan kata sandi database yang Anda buat di file `.env` (`DB_PASSWORD` atau `DB_ROOT_PASSWORD`).
   - **Default Schema:** `kobana_pos`
4. Klik tombol **Test Connection** di bagian pojok kanan bawah.
5. Dialog akan muncul: **"Successfully made the MySQL connection"**.
6. Klik **OK** untuk menyimpan. Sekarang Anda dapat membuka koneksi dan mengelola seluruh tabel POS, cabang, dan data mining FP-Growth secara visual!

---

### 6.2 Metode 2: Standard TCP/IP (Direct Connection Tanpa SSH)

Gunakan metode ini jika Anda tidak memiliki akses SSH langsung atau ingin menghubungkan aplikasi desktop/BI tools pihak ketiga via IP publik.

#### Langkah 1: Atur Binding IP di VPS
Buka file `.env` di direktori proyek VPS:
```bash
cd /var/www/kobana-pos
nano .env
```
Ubah parameter `DB_BIND_IP`:
```dotenv
DB_BIND_IP=0.0.0.0
FORWARD_DB_PORT=3306
```
Simpan file (`CTRL + O`, `Enter`, `CTRL + X`), lalu terapkan perubahan kontainer:
```bash
docker compose up -d db
```

#### Langkah 2: Buka Port 3306 pada Firewall UFW
Demi keamanan, sangat disarankan untuk hanya mengizinkan IP publik komputer Anda:
```bash
# Opsi Rekomendasi (Hanya IP Rumah / Kantor Anda yang boleh connect):
ufw allow from IP_PUBLIK_KOMPUTER_ANDA to any port 3306 proto tcp

# Atau Opsi Terbuka (Buka untuk seluruh IP publik):
ufw allow 3306/tcp
ufw status
```

#### Langkah 3: Konfigurasi di MySQL Workbench
1. Klik tanda **`+`** pada MySQL Connections.
2. Isi data:
   - **Connection Name:** `Kobana POS VPS (Direct)`
   - **Connection Method:** Pilih **`Standard TCP/IP`**
   - **Hostname:** `IP_VPS_ANDA`
   - **Port:** `3306`
   - **Username:** `kobana_user`
   - **Password:** Masukkan password database Anda di *Store in Vault...*
   - **Default Schema:** `kobana_pos`
3. Masuk ke tab **Advanced**:
   - Pada kotak isian **Others:**, ketikkan parameter:
     ```
     allowPublicKeyRetrieval=true
     ```
4. Masuk ke tab **SSL**:
   - **Use SSL:** Pilih `If available` atau `Require`.
5. Klik **Test Connection**, lalu klik **OK**.

---

### 6.3 Optimasi Khusus Docker untuk MySQL Workbench yang Telah Diterapkan

Di dalam konfigurasi Docker Kobana POS (`docker/mysql/my.cnf`), beberapa parameter penting telah diaktifkan khusus untuk kenyamanan dan kestabilan MySQL Workbench:

1. **`skip-name-resolve = 1`**:
   - Mematikan DNS reverse-lookup pada setiap koneksi masuk.
   - Hasil: Membuka daftar tabel (*Object Browser*) dan skema database di MySQL Workbench berlangsung instan tanpa delay 5-10 detik.
2. **`interactive_timeout = 28800` & `wait_timeout = 28800` (8 Jam)**:
   - Mencegah error menyebalkan: *"Error Code: 2006. MySQL server has gone away"*. Sesi Workbench Anda tidak akan terputus meskipun ditinggal istirahat atau tidak ada query selama berjam-jam.
3. **`max_allowed_packet = 64M`**:
   - Menghindari error saat Anda melakukan ekspor atau impor file `.sql` backup berukuran besar langsung melalui menu *Data Export / Data Import* di MySQL Workbench.
