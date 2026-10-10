# 📖 Panduan Penggunaan Sistem POS & Market Basket Analysis (FP-Growth)
### Kobana Fried Chicken

Dokumen ini berisi panduan lengkap dan langkah demi langkah pengoperasian setiap fitur yang ada di sistem **Kobana Fried Chicken**.

---

## 📑 Daftar Isi
1. [Akses Sistem & Akun Pengguna](#1-akses-sistem--akun-pengguna)
2. [Dashboard Analitik](#2-dashboard-analitik)
3. [Kasir / Input Transaksi Baru (POS)](#3-kasir--input-transaksi-baru-pos)
4. [Riwayat & Detail Transaksi](#4-riwayat--detail-transaksi)
5. [Master Menu & Master Kategori](#5-master-menu--master-kategori)
6. [Analisis FP-Growth (Market Basket Analysis)](#6-analisis-fp-growth-market-basket-analysis)
7. [Laporan Penjualan & Ekspor (Excel/PDF)](#7-laporan-penjualan--ekspor-excelpdf)
8. [Impor Dataset Penjualan (Excel)](#8-impor-dataset-penjualan-excel)
9. [Manajemen Pengguna (User) & Profil](#9-manajemen-pengguna-user--profil)
10. [Tips & Troubleshooting](#10-tips--troubleshooting)

---

## 1. Akses Sistem & Akun Pengguna

### A. Alamat Akses
- **Lokal XAMPP:** `http://localhost/antigravity/public`
- **Artisan Serve:** `http://localhost:8000`

### B. Matriks Hak Akses (Role)

| Fitur | Admin | Kasir | Owner |
|---|:---:|:---:|:---:|
| **Dashboard Analitik** | ✅ | ✅ | ✅ |
| **Kasir / Input Transaksi** | ✅ | ✅ | ❌ |
| **Riwayat Transaksi** | ✅ (Full + Edit/Hapus) | ✅ (Lihat/Cetak) | ✅ (Lihat/Cetak) |
| **Master Menu & Kategori** | ✅ (Full CRUD) | ✅ (Lihat saja) | ✅ (Lihat saja) |
| **Analisis FP-Growth** | ✅ (Jalankan & Hapus) | ❌ | ✅ (Jalankan & Lihat) |
| **Laporan & Ekspor** | ✅ | ❌ | ✅ |
| **Impor Dataset Excel** | ✅ | ❌ | ❌ |
| **Manajemen Pengguna** | ✅ | ❌ | ❌ |

### C. Akun Bawaan (Default)
| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@kobana.id` | `password` |
| **Kasir** | `kasir@kobana.id` | `password` |
| **Owner** | `owner@kobana.id` | `password` |

---

## 2. Dashboard Analitik
**Menu Sidebar:** `Utama` ➔ `Dashboard` (`/dashboard`)

Fitur ini menyajikan visualisasi data penjualan secara real-time dan komprehensif untuk memantau performa bisnis.

### Cara Menggunakan:
1. **Memilih Rentang Waktu:**
   - Isi input **Dari Tanggal** dan **Sampai Tanggal** di bagian atas halaman (secara bawaan menampilkan 30 hari terakhir).
   - Klik tombol **Terapkan** untuk menyaring data.
   - Klik tombol ikon *refresh* untuk mengembalikan ke tanggal default.
2. **Membaca Kartu Ringkasan:**
   - **Omzet Periode:** Total pendapatan kotor dalam periode yang dipilih.
   - **Total Transaksi:** Jumlah transaksi penjualan yang berhasil dicatat.
   - **Item Terjual:** Total kuantitas porsi/produk yang terjual.
   - **Rata-rata Trx:** Rata-rata nominal belanja per transaksi (*Average Order Value*).
   - **Omzet Hari Ini & Trx Hari Ini:** Pendapatan dan jumlah transaksi khusus hari ini.
   - **Total Menu & Kategori:** Jumlah master data produk yang terdaftar.
3. **Menganalisis Grafik:**
   - **Tren Omzet Harian (Kombinasi Line & Bar):** Memantau fluktuasi omzet dan frekuensi transaksi harian.
   - **Omzet per Jam Hari Ini (Bar):** Mengetahui jam sibuk (*peak hours*) operasional kasir.
   - **Omzet per Hari dalam Seminggu (Horizontal Bar):** Mengidentifikasi hari dengan penjualan tertinggi (misal: akhir pekan vs hari kerja).
   - **Top 10 Menu Terlaris:** Mengetahui menu favorit yang memberikan kontribusi omzet terbesar.
   - **Distribusi Omzet per Kategori (Donut Chart):** Mengetahui persentase kontribusi kategori produk (misal: Paket Ayam, Minuman, dsb).

---

## 3. Kasir / Input Transaksi Baru (POS)
**Menu Sidebar:** `Transaksi` ➔ `Kasir / Input Transaksi` (`/transactions/create`)

Antarmuka Point of Sale (POS) cepat dan interaktif untuk melayani pesanan pelanggan.

### Cara Melakukan Transaksi:
1. **Cari Menu:** Ketikkan nama atau kode menu pada kotak pencarian di tabel menu (misal: `Ayam`, `Teh`, atau `M01`).
2. **Tentukan Jumlah & Diskon:**
   - Pada baris menu yang dipilih, masukkan angka pada kolom **Qty** (jumlah pesanan).
   - Masukkan nominal diskon per item (jika ada potongan promo) pada kolom **Diskon**.
3. **Masukkan ke Keranjang:**
   - Klik tombol hijau **+ Tambah**. Item akan langsung masuk ke tabel **Keranjang Transaksi** di sebelah kanan.
   - Jika menu yang sama ditambahkan lagi, jumlah qty akan otomatis diakumulasikan.
4. **Mengubah Pesanan di Keranjang:**
   - Klik tombol **`+`** atau **`-`** untuk menambah/mengurangi qty secara instan.
   - Klik tombol silang merah **`✕`** untuk menghapus menu tertentu dari keranjang.
   - Klik tombol **Kosongkan** jika ingin membatalkan seluruh pesanan sekaligus.
5. **Memproses Pembayaran:**
   - Periksa **Total Jumlah Item** dan **Total Pembayaran**.
   - Klik tombol hijau **Proses & Simpan Transaksi**.
   - Muncul dialog konfirmasi SweetAlert2: klik **Ya, Simpan Transaksi**.
   - Sistem akan mengunci urutan nomor transaksi (*DB Row Locking*) dan menghasilkan kode otomatis dengan format `TRXYYYYMMDDXXXXX` (contoh: `TRX2026101000001`).
   - Anda akan diarahkan langsung ke halaman **Detail Transaksi / Nota**.

---

## 4. Riwayat & Detail Transaksi
**Menu Sidebar:** `Transaksi` ➔ `Riwayat Transaksi` (`/transactions`)

### A. Melihat & Menyaring Riwayat
1. Masuk ke menu **Riwayat Transaksi**.
2. Gunakan kolom filter untuk mencari data tertentu:
   - **Dari Tanggal & Sampai Tanggal:** Rentang tanggal transaksi.
   - **Kode Transaksi:** Pencarian spesifik kode (misal `TRX202610`).
   - **Nama Menu:** Mencari transaksi yang memuat menu tertentu.
3. Klik tombol **Filter** untuk menjalankan pencarian.

### B. Melihat Rincian & Mencetak Struk
1. Klik tombol ikon mata (biru) atau klik Kode Transaksi pada tabel.
2. Pada halaman **Detail Transaksi**:
   - Tampil rincian lengkap: waktu transaksi, nama kasir, item menu, harga, diskon, subtotal, dan total pembayaran.
   - Klik tombol **Cetak Struk** untuk langsung membuka jendela cetak printer kasir / simpan PDF nota.

### C. Mengedit & Menghapus Transaksi *(Khusus Admin)*
- **Edit Transaksi:** Klik tombol **Edit** ➔ Keranjang pesanan lama akan dimuat ulang ke layar POS ➔ Ubah item/qty/diskon ➔ Klik **Update Transaksi**.
- **Hapus Transaksi:** Klik tombol **Hapus Transaksi** ➔ Konfirmasi dialog SweetAlert2 ➔ Transaksi dan itemnya akan dihapus permanen dari database.

---

## 5. Master Menu & Master Kategori

### A. Master Kategori (`/categories`)
Kategori digunakan untuk mengelompokkan menu (misal: *Ayam Goreng, Paket Nasi, Minuman, Camilan*).
1. **Tambah Kategori:** Klik **+ Tambah Kategori** ➔ Masukkan nama kategori ➔ Klik **Simpan**.
2. **Edit Kategori:** Klik tombol pensil ➔ Ubah nama ➔ Klik **Simpan**.
3. **Hapus Kategori:** Klik tombol tempat sampah merah.
   > ⚠️ *Catatan Proteksi:* Kategori yang masih memiliki produk menu terdaftar **tidak dapat dihapus** sebelum semua menu di dalamnya dipindahkan atau dihapus terlebih dahulu.

### B. Master Menu (`/menus`)
1. **Tambah Menu:**
   - Klik **+ Tambah Menu Baru**.
   - Masukkan **Kode Menu** (sistem otomatis memberikan rekomendasi kode urut seperti `M01`, `M02`).
   - Masukkan **Nama Menu**, pilih **Kategori**, dan ketikkan **Harga Satuan** (Rp).
   - Centang opsi **Status Aktif**.
   - Klik **Simpan Menu**.
2. **Mengubah Status Aktif / Nonaktif:**
   - Klik tombol ikon mata (kuning/hijau) pada kolom aksi untuk mengaktifkan atau menonaktifkan menu tanpa menghapusnya. Menu nonaktif tidak akan muncul di layar POS kasir.
3. **Hapus Menu:**
   - Klik tombol sampah merah.
   - Jika menu **belum pernah ditransaksikan**, menu dihapus permanen.
   - Jika menu **sudah memiliki riwayat transaksi**, sistem otomatis melakukan *Soft-Delete* (mengarsipkan menu dan menonaktifkannya) agar histori laporan penjualan masa lalu tetap utuh dan tidak rusak.

---

## 6. Analisis FP-Growth (Market Basket Analysis)
**Menu Sidebar:** `Analisis & Laporan` ➔ `Analisis FP-Growth` (`/analysis/fp-growth`)

Fitur cerdas *Data Mining* murni PHP untuk menemukan pola menu yang sering dibeli bersamaan oleh pelanggan.

### A. Menjalankan Analisis Baru:
1. Pada form **Jalankan Analisis Baru**, isi parameter berikut:
   - **Nama Analisis:** Judul identifikasi (contoh: `Analisis Penjualan Q3 2026`).
   - **Dari Tanggal & Sampai Tanggal:** Periode transaksi yang ingin ditambang.
   - **Min. Support (Nilai antara 0.01 - 1):** Persentase batas minimal seberapa sering kombinasi menu muncul dalam seluruh transaksi.
     - *Contoh: `0.05` berarti kombinasi harus muncul minimal di 5% total transaksi.*
   - **Min. Confidence (Nilai antara 0.01 - 1):** Batas minimal kepastian/peluang aturan berlaku.
     - *Contoh: `0.60` berarti jika pembeli membeli Menu A, minimal 60% dari mereka juga pasti membeli Menu B.*
2. Klik tombol biru **Jalankan Analisis**.
3. Sistem akan membangun pohon **FP-Tree**, menambang *Frequent Itemsets*, dan mengekstraksi *Association Rules* secara sinkron.

### B. Membaca Hasil & Wawasan (Insight):
1. **Grafik Top Rules:** Menampilkan perbandingan nilai **Lift** dan **Confidence** untuk 10 aturan asosiasi terkuat.
2. **Tabel Frequent Itemsets:** Menampilkan kelompok item yang frekuensi pembeliannya di atas ambang batas *Support*.
3. **Tabel Association Rules:**
   - **Antecedent (Jika Membeli):** Menu pemicu.
   - **Consequent (Maka Membeli):** Menu yang cenderung ikut dibeli.
   - **Nilai Lift:**
     - **Lift > 1.5 (Badge Hijau / Kuat):** Asosiasi sangat kuat. Sangat direkomendasikan dibuat **Paket Bundling Promosi** atau penataan menu berdampingan.
     - **Lift 1.0 - 1.5 (Badge Biru / Positif):** Ada korelasi positif antar produk.
     - **Lift < 1.0 (Badge Kuning / Lemah):** Produk tidak memiliki keterikatan pembelian.

---

## 7. Laporan Penjualan & Ekspor (Excel/PDF)
**Menu Sidebar:** `Analisis & Laporan` ➔ `Laporan Penjualan` (`/reports`)

Digunakan oleh Owner dan Admin untuk rekapitulasi data akuntansi & penjualan berkala.

### Cara Menggunakan:
1. **Filter Laporan:**
   - Tentukan **Dari Tanggal** & **Sampai Tanggal**.
   - Pilih filter **Kategori** jika hanya ingin melihat kategori tertentu.
   - Klik **Filter**.
2. **Melihat Ringkasan:**
   - Bagian atas menampilkan ringkasan: Total Omzet, Total Transaksi, Item Terjual, dan Rata-rata Belanja.
   - Tabel **Laporan Harian:** Rincian transaksi dan omzet per hari kalender.
   - Tabel **Laporan per Menu:** Rincian kuantitas terjual dan subtotal omzet per masing-masing menu.
3. **Ekspor Data:**
   - **Ekspor CSV / Excel:** Klik tombol hijau **Ekspor CSV / Excel** untuk mengunduh spreadsheet `.csv` (dilengkapi UTF-8 BOM sehingga langsung terbuka rapi di Microsoft Excel tanpa teks berantakan).
   - **Ekspor PDF (Print):** Klik tombol merah **Ekspor PDF (Print)** untuk membuka tampilan cetak laporan bersih, siap dicetak ke kertas fisik atau disimpan menjadi file PDF (*Save as PDF*).

---

## 8. Impor Dataset Penjualan (Excel)
**Menu Sidebar:** `Sistem` ➔ `Impor Dataset` (`/imports`) *(Khusus Admin)*

Digunakan untuk migrasi data awal atau mengunggah rekapan data transaksi massal dari file Microsoft Excel.

### A. Struktur File Excel (.xlsx / .xls)
File Excel harus memiliki 3 lembar kerja (Sheet) berikut:
1. **Sheet `Master_Menu`:** Kolom `Kode`, `Menu`, `Kategori`, `Harga`
2. **Sheet `Header_Transaksi`:** Kolom `ID`, `Tanggal`, `Hari`, `Jam`, `Total_Item`, `Total_Bayar`
3. **Sheet `Detail_Transaksi`:** Kolom `ID`, `Kode`, `Menu`, `Qty`, `Harga`, `Subtotal`

### B. Langkah Impor:
1. Klik tombol **Pilih File (.xlsx / .xls)** dan pilih file Excel yang sesuai.
2. Klik tombol biru **Mulai Impor Dataset**.
3. Muncul indikator proses loading SweetAlert.
4. Setelah selesai, notifikasi sukses akan menginformasikan jumlah menu, transaksi, dan total omzet yang berhasil masuk ke database.
5. Proses impor bersifat **Idempoten** (aman diunggah ulang tanpa menggandakan transaksi yang sama).

---

## 9. Manajemen Pengguna (User) & Profil
**Menu Sidebar:** `Sistem` ➔ `Manajemen User` (`/users`) *(Khusus Admin)*

### A. Menambah Pengguna Baru:
1. Klik **+ Tambah Pengguna**.
2. Masukkan **Nama Lengkap**, **Alamat Email**, **Password** (minimal 6 karakter).
3. Pilih **Peran (Role)**:
   - `Admin`: Akses penuh ke seluruh sistem.
   - `Kasir`: Fokus pada transaksi POS & riwayat.
   - `Owner`: Akses monitoring dashboard, analisis data, dan laporan.
4. Centang **Status Aktif** lalu klik **Simpan Pengguna**.

### B. Mengubah Status & Hapus Pengguna:
- Klik tombol mata/gembok untuk mengaktifkan atau menonaktifkan akun karyawan yang sudah tidak bekerja.
- Sistem memiliki **Proteksi Akun Sendiri** (Anda tidak dapat menonaktifkan atau menghapus akun Anda sendiri saat sedang login).

### C. Ganti Password Pribadi:
1. Klik foto profil / nama Anda di pojok kanan atas navbar ➔ Pilih **Ganti Password** (`/profile/password`).
2. Masukkan password saat ini dan password baru minimal 6 karakter.
3. Klik **Perbarui Password**.

---

## 10. Tips & Troubleshooting

1. **Kasir Tidak Sengaja Menutup Browser saat Input:**
   - Data sebelum tombol *Proses Transaksi* diklik belum tersimpan di server. Buka kembali menu kasir untuk memulai transaksi baru.
2. **Nilai Analisis FP-Growth Kosong:**
   - Jika hasil analisis FP-Growth tidak memunculkan *rules*, artinya nilai **Min. Support** atau **Min. Confidence** yang dimasukkan terlalu tinggi. Coba turunkan nilai (misal: Support menjadi `0.02` atau `0.03` dan Confidence menjadi `0.40`).
3. **Koneksi Database:**
   - Aplikasi mendukung penuh driver **SQLite** (untuk kemudahan instalasi lokal tanpa konfigurasi) dan **MySQL/MariaDB** (untuk server produksi multi-user) tanpa perlu merubah logika kode.
4. **Logout Aman:**
   - Selalu gunakan menu dropdown di kanan atas ➔ Klik **Logout** saat selesai bertugas di kasir untuk mencegah penyalahgunaan akun.

---
*Dokumentasi ini disusun untuk Kobana Fried Chicken — POS & Market Basket Analysis System.*
