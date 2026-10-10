# Kobana Fried Chicken — Sistem Manajemen & FP-Growth

> **Aplikasi POS + Market Basket Analysis** berbasis Laravel untuk **Kobana Fried Chicken**.  
> Dibangun dengan Laravel 11, Bootstrap 5, Chart.js 4, dan algoritma FP-Growth murni PHP.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi Database](#konfigurasi-database)
- [Struktur Role Pengguna](#struktur-role-pengguna)
- [Panduan Penggunaan](#panduan-penggunaan)
- [Menjalankan Test](#menjalankan-test)
- [Struktur Direktori Penting](#struktur-direktori-penting)
- [Algoritma FP-Growth](#algoritma-fp-growth)

---

## Fitur Utama

| Fase | Fitur |
|------|-------|
| **1–5** | Autentikasi, manajemen user (admin/kasir/owner), master kategori & menu, impor dataset CSV |
| **6** | POS Kasir interaktif – penomoran otomatis `TRXYYYYMMDDXXXXX`, kalkulasi real-time, validasi server-side |
| **7** | **Dashboard Analitik** – 8 kartu ringkasan, 5 grafik Chart.js (tren harian, per-jam, per-hari-minggu, top menu, donut kategori), filter rentang tanggal |
| **8** | **Analisis FP-Growth** – FP-Tree murni PHP, frequent itemsets, association rules (Support, Confidence, Lift), antarmuka hasil lengkap |
| **9** | **Laporan & Ekspor** – laporan harian & per-menu, ekspor CSV/Excel, ekspor PDF (print browser) |
| **10** | Test otomatis (PHPUnit), format kode Pint |

---

## Teknologi

- **Backend**: PHP 8.3+, Laravel 11
- **Frontend**: Bootstrap 5.3, Bootstrap Icons, Chart.js 4, SweetAlert2
- **Database**: MySQL / MariaDB
- **Queue**: Laravel sync queue (dapat dikonfigurasi ke Redis/database)
- **Testing**: PHPUnit (via `php artisan test`)
- **Code Style**: Laravel Pint

---

## Persyaratan Sistem

| Komponen | Versi Minimum |
|----------|---------------|
| PHP | 8.3 |
| Composer | 2.x |
| MySQL / MariaDB | 8.0 / 10.6 |
| Node.js (opsional, untuk build asset) | 18+ |

---

## Instalasi

```bash
# 1. Clone / ekstrak repositori
cd C:/xampp/htdocs
# (pastikan folder antigravity sudah ada)

# 2. Install dependency PHP
composer install

# 3. Salin file environment
copy .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Jalankan migrasi & seeder
php artisan migrate --seed

# 6. (Opsional) Jalankan development server
php artisan serve
```

Akses aplikasi di: **http://localhost:8000** (atau **http://localhost/antigravity/public** via XAMPP)

---

## Konfigurasi Database

Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kobana_pos
DB_USERNAME=root
DB_PASSWORD=
```

---

## Struktur Role Pengguna

| Role | Hak Akses |
|------|-----------|
| **admin** | Akses penuh – semua fitur termasuk edit/hapus transaksi, manajemen user, analisis FP-Growth, laporan |
| **kasir** | Input transaksi (POS), lihat riwayat transaksi |
| **owner** | Dashboard analitik, analisis FP-Growth, laporan (read-only) |

**Akun default** (setelah seeder):

| Email | Password | Role |
|-------|----------|------|
| `admin@kobana.id` | `password` | admin |
| `kasir@kobana.id` | `password` | kasir |
| `owner@kobana.id` | `password` | owner |

---

## Panduan Penggunaan

### 🖥️ Dashboard Analitik (`/dashboard`)

- Filter rentang tanggal di atas halaman.
- **Kartu ringkasan**: Omzet periode, total transaksi, item terjual, rata-rata transaksi, omzet hari ini, transaksi hari ini, total menu, total kategori.
- **Grafik**:
  - Tren omzet harian (line + bar combo)
  - Omzet per jam hari ini
  - Omzet per hari dalam seminggu
  - Top 10 menu terlaris
  - Donut distribusi kategori

### 🛒 POS Kasir (`/transactions/create`)

1. Pilih menu dari tabel (klik baris).
2. Atur jumlah dan diskon per item.
3. Klik **Tambah ke Keranjang**.
4. Klik **Proses Transaksi** → konfirmasi SweetAlert2 → simpan.

### 📈 Analisis FP-Growth (`/analysis/fp-growth`) *(admin & owner)*

1. Isi nama analisis, rentang tanggal, `min_support` dan `min_confidence` (antara 0–1).
2. Klik **Jalankan Analisis** (proses sinkron, tunggu selesai).
3. Lihat hasil: frequent itemsets + association rules dengan Lift, Confidence, dan badge insight (Kuat/Positif/Lemah).

### 📄 Laporan & Ekspor (`/reports`) *(admin & owner)*

- Filter per tanggal dan kategori.
- **Ekspor CSV** – kompatibel dengan Microsoft Excel.
- **Ekspor PDF** – membuka halaman print di browser, simpan sebagai PDF.

---

## Menjalankan Test

```bash
# Jalankan semua test
php artisan test

# Jalankan dengan detail
php artisan test --verbose

# Jalankan hanya unit test FP-Growth
php artisan test --filter FpGrowthServiceTest

# Format kode dengan Pint
./vendor/bin/pint
```

**Test yang tersedia:**

| File | Cakupan |
|------|---------|
| `tests/Feature/AuthAndUserManagementTest.php` | Login, logout, manajemen user |
| `tests/Feature/MasterDataTest.php` | CRUD kategori & menu |
| `tests/Feature/DashboardAnalyticsTest.php` | Dashboard stats, filter tanggal, auth guard |
| `tests/Feature/ReportTest.php` | Laporan index, ekspor CSV, filter, akses role |
| `tests/Unit/FpGrowthServiceTest.php` | FP-Growth service, model relationships, status |

---

## Struktur Direktori Penting

```
app/
├── Http/Controllers/
│   ├── DashboardController.php        # Analytics dashboard
│   ├── TransactionController.php      # POS & transaksi
│   ├── FpGrowthController.php         # FP-Growth analysis
│   ├── ReportController.php           # Laporan & ekspor
│   ├── MenuController.php
│   ├── CategoryController.php
│   └── UserController.php
├── Jobs/
│   └── RunFpGrowthJob.php             # Queue job FP-Growth
├── Models/
│   ├── Transaction.php
│   ├── TransactionItem.php
│   ├── FpAnalysis.php
│   ├── FpFrequentItemset.php
│   └── FpAssociationRule.php
└── Services/
    └── FpGrowthService.php            # Algoritma FP-Growth murni PHP

resources/views/
├── dashboard/index.blade.php          # Dashboard + Chart.js
├── transactions/{create,index,show,edit}.blade.php
├── analysis/fp-growth/{index,show}.blade.php
├── reports/{index,pdf}.blade.php
└── layouts/app.blade.php

database/migrations/
├── ..._create_transactions_and_items_tables.php
├── ..._create_transaction_sequences_table.php
└── ..._create_fp_analysis_tables.php
```

---

## Algoritma FP-Growth

Implementasi murni PHP tanpa library eksternal di [`app/Services/FpGrowthService.php`](app/Services/FpGrowthService.php).

### Pipeline

```
Transaksi DB
    │
    ▼
1. Hitung frekuensi 1-item → filter < minSupport
    │
    ▼
2. Sort tiap transaksi berdasarkan frekuensi ↓
    │
    ▼
3. Build FP-Tree (FpTreeNode dengan header table)
    │
    ▼
4. Mining → conditional pattern base per item
    │
    ▼
5. Frequent Itemsets → simpan ke fp_frequent_itemsets
    │
    ▼
6. Generate Association Rules (subset enumeration)
   Support = count(AB) / totalTrx
   Confidence = count(AB) / count(A)
   Lift = Confidence / (count(B) / totalTrx)
    │
    ▼
7. Simpan rules ke fp_association_rules
```

### Interpretasi Lift

| Lift | Interpretasi |
|------|-------------|
| > 1.5 | Kuat – pembelian bersama terjadi jauh lebih sering dari kebetulan |
| 1 – 1.5 | Positif – ada korelasi positif |
| < 1 | Lemah – item cenderung **tidak** dibeli bersama |

---

## Lisensi

Proyek ini dibuat untuk keperluan akademis / tugas akhir Kobana Fried Chicken.  
&copy; 2025–2026 Kobana Fried Chicken.
