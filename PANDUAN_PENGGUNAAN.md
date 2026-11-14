# 📖 Panduan Penggunaan Sistem POS & Market Basket Analysis (FP-Growth)
### Kobana Fried Chicken

Dokumen ini berisi panduan lengkap dan langkah demi langkah pengoperasian setiap fitur yang ada di sistem **Kobana Fried Chicken**.

---

## 📑 Daftar Isi
1. [Akses Sistem & Akun Pengguna](#1-akses-sistem--akun-pengguna)
2. [Dashboard Analitik](#2-dashboard-analitik)
3. [Kasir / Input Transaksi Baru (POS)](#3-kasir--input-transaksi-baru-pos)
4. [Riwayat & Detail Transaksi](#4-riwayat--detail-transaksi)
5. [Kas & Belanja Harian Warung (Petty Cash & Tabungan)](#5-kas--belanja-harian-warung-petty-cash--tabungan)
6. [Master Menu & Master Kategori](#6-master-menu--master-kategori)
7. [Analisis FP-Growth (Market Basket Analysis)](#7-analisis-fp-growth-market-basket-analysis)
8. [Laporan Penjualan & Ekspor (Excel/PDF)](#8-laporan-penjualan--ekspor-excelpdf)
9. [Impor Dataset Penjualan (Excel)](#9-impor-dataset-penjualan-excel)
10. [Manajemen Pengguna (User) & Profil](#10-manajemen-pengguna-user--profil)
11. [Tips & Troubleshooting](#11-tips--troubleshooting)

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
| **Kas & Belanja Harian** | ✅ (Full) | ✅ (Catat/Lihat Cabang) | ✅ (Monitoring Seluruh Cabang) |
| **Master Menu & Kategori** | ✅ (Full CRUD) | ✅ (Lihat saja) | ✅ (Lihat saja) |
| **Analisis FP-Growth** | ✅ (Jalankan & Hapus) | ❌ | ✅ (Jalankan & Lihat) |
| **Laporan & Ekspor** | ✅ | ❌ | ✅ |
| **Impor Dataset Excel** | ✅ | ❌ | ❌ |
| **Manajemen Pengguna** | ✅ | ❌ | ❌ |

### C. Akun Bawaan & Penugasan Cabang (3 Cabang)
Kobana Fried Chicken memiliki 3 cabang outlet resmi:
1. **Kobana Pusat (`KBN01`)** — Jl. Soekarno Hatta No. 12 (Telp: 0812-3456-7890)
2. **Kobana Sukun (`KBN02`)** — Jl. S. Supriadi No. 45 (Telp: 0812-3456-7891)
3. **Kobana Dinoyo (`KBN03`)** — Jl. MT Haryono No. 88 (Telp: 0812-3456-7892)

| Role | Akun Email | Password | Penugasan Cabang |
|---|---|---|---|
| **Admin** | `admin@kobana.id` | `password` | Kantor Pusat / Semua Cabang |
| **Kasir 1** | `kasir@kobana.id` | `password` | Cabang Kobana Pusat (`KBN01`) |
| **Kasir 2** | `kasir2@kobana.id` | `password` | Cabang Kobana Sukun (`KBN02`) |
| **Kasir 3** | `kasir3@kobana.id` | `password` | Cabang Kobana Dinoyo (`KBN03`) |
| **Owner** | `owner@kobana.id` | `password` | Pemilik / Monitoring Semua Cabang |

---

## 2. Dashboard Analitik
**Menu Sidebar:** `Utama` ➔ `Dashboard` (`/dashboard`)

Fitur ini menyajikan visualisasi data penjualan secara real-time dan komprehensif untuk memantau performa bisnis, baik secara menyeluruh maupun per cabang.

### Cara Menggunakan:
1. **Memilih Rentang Waktu & Cabang:**
   - Isi input **Dari Tanggal** dan **Sampai Tanggal** di bagian atas halaman (secara bawaan menampilkan 30 hari terakhir).
   - **Filter Cabang (Khusus Owner & Admin):** Pilih salah satu cabang (Kobana Pusat, Kobana Sukun, Kobana Dinoyo) atau pilih *Semua Cabang* untuk melihat performa gabungan. Kasir yang login otomatis terkunci ke cabangnya masing-masing.
   - Klik tombol **Terapkan** untuk menyaring data.
   - Klik tombol ikon *refresh* untuk mengembalikan ke tanggal default.
2. **Membaca Kartu Ringkasan:**
   - **Omzet Periode:** Total pendapatan kotor dalam periode dan cabang yang dipilih.
   - **Total Transaksi:** Jumlah transaksi penjualan yang berhasil dicatat.
   - **Item Terjual:** Total kuantitas porsi/produk yang terjual.
   - **Rata-rata Trx:** Rata-rata nominal belanja per transaksi (*Average Order Value*).
   - **Omzet Hari Ini & Trx Hari Ini:** Pendapatan dan jumlah transaksi khusus hari ini.
   - **Total Menu & Kategori:** Jumlah master data produk yang terdaftar.
3. **Membaca Rincian Metode Pembayaran:**
   - **Cash (Tunai):** Menampilkan total omzet tunai, jumlah transaksi, dan kontribusi persentase terhadap total omzet.
   - **QRIS:** Menampilkan total omzet digital QRIS, jumlah transaksi, dan kontribusi persentase.
4. **Menganalisis Grafik:**
   - **Tren Omzet Harian (Kombinasi Line & Bar):** Memantau fluktuasi omzet dan frekuensi transaksi harian.
   - **Omzet per Jam Hari Ini (Bar):** Mengetahui jam sibuk (*peak hours*) operasional kasir.
   - **Omzet per Hari dalam Seminggu (Horizontal Bar):** Mengidentifikasi hari dengan penjualan tertinggi (misal: akhir pekan vs hari kerja).
   - **Top 10 Menu Terlaris:** Mengetahui menu favorit yang memberikan kontribusi omzet terbesar.
   - **Distribusi Omzet per Kategori (Donut Chart):** Mengetahui persentase kontribusi kategori produk (misal: Paket Ayam, Minuman, dsb).

---

## 3. Kasir / Input Transaksi Baru (POS)
**Menu Sidebar:** `Transaksi` ➔ `Kasir / Input Transaksi` (`/transactions/create`)

Antarmuka Point of Sale (POS) cepat dan interaktif untuk melayani pesanan pelanggan dengan dukungan multi-cabang dan metode pembayaran (Cash dan QRIS).

### Cara Melakukan Transaksi:
1. **Pemeriksaan Cabang & Kasir:**
   - Di bagian atas layar POS terdapat banner informasi cabang tempat kasir bertugas (misal: *Kobana Pusat*, *Kobana Sukun*, atau *Kobana Dinoyo*). Kasir otomatis mengunci transaksi ke cabang penugasannya.
   - Jika login sebagai Admin, Admin dapat memilih cabang tujuan transaksi melalui dropdown.
2. **Cari Menu:** Ketikkan nama atau kode menu pada kotak pencarian di tabel menu (misal: `Ayam`, `Teh`, atau `M01`).
3. **Tentukan Jumlah & Diskon:**
   - Pada baris menu yang dipilih, masukkan angka pada kolom **Qty** (jumlah pesanan).
   - Masukkan nominal diskon per item (jika ada potongan promo) pada kolom **Diskon**.
4. **Masukkan ke Keranjang:**
   - Klik tombol hijau **+ Tambah**. Item akan langsung masuk ke tabel **Keranjang Transaksi** di sebelah kanan.
   - Jika menu yang sama ditambahkan lagi, jumlah qty akan otomatis diakumulasikan.
5. **Mengubah Pesanan di Keranjang:**
   - Klik tombol **`+`** atau **`-`** untuk menambah/mengurangi qty secara instan.
   - Klik tombol silang merah **`✕`** untuk menghapus menu tertentu dari keranjang.
   - Klik tombol **Kosongkan** jika ingin membatalkan seluruh pesanan sekaligus.
6. **Memilih Metode Pembayaran:**
   - **Cash (Tunai):**
     - Masukkan jumlah uang tunai yang diterima pelanggan pada kotak *Uang Tunai Diterima (Rp)*.
     - Atau klik tombol cepat (*Uang Pas*, *20rb*, *50rb*, *100rb*).
     - Sistem otomatis menghitung dan menampilkan nominal **Kembalian** secara real-time. Jika uang kurang, sistem memberi peringatan merah.
   - **QRIS:**
     - Klik opsi **QRIS**.
     - Tunjukkan QR code fisik/meja kasir atau dinamis ke pelanggan.
     - Kasir dapat memasukkan nomor referensi / RRN struk QRIS (opsional) pada kotak yang disediakan untuk pencocokan mutasi.
     - **Foto Bukti Transaksi QRIS (Fitur Kamera / Unggah Screenshot):**
       - Klik tombol **Buka Kamera** untuk mengaktifkan webcam laptop/PC kasir atau kamera perangkat HP/tablet secara langsung.
       - Arahkan ke layar HP pelanggan yang menampilkan bukti sukses QRIS atau struk kasir, lalu klik **Ambil Foto**.
       - Atau klik tombol **Unggah File** jika kasir menerima file gambar / screenshot bukti pembayaran.
       - Foto pratinjau (*Foto Siap*) akan tampil dan dapat dihapus/diambil ulang kapan saja dengan tombol **Hapus**.
     - Tidak memerlukan kembalian (otomatis nominal pas).
7. **Memproses & Menyimpan Transaksi:**
   - Periksa kembali ringkasan pesanan dan nominal pembayaran.
   - Klik tombol **Proses & Simpan Transaksi**.
   - Muncul dialog konfirmasi SweetAlert2: klik **Ya, Simpan Transaksi**.
   - Sistem mengunci nomor transaksi (*DB Row Locking*) dan menghasilkan kode unik `TRXYYYYMMDDXXXXX` (contoh: `TRX2026101000001`).
   - Anda akan diarahkan langsung ke halaman **Detail Transaksi / Cetak Struk**.

---

## 4. Riwayat & Detail Transaksi
**Menu Sidebar:** `Transaksi` ➔ `Riwayat Transaksi` (`/transactions`)

### A. Melihat & Menyaring Riwayat
1. Masuk ke menu **Riwayat Transaksi**.
   - **Perilaku Kasir:** Kasir hanya dapat melihat riwayat transaksi dari cabang tempatnya bertugas.
   - **Perilaku Admin & Owner:** Dapat melihat seluruh transaksi dan memfilter berdasarkan cabang mana pun.
2. Gunakan kolom filter untuk mencari data tertentu:
   - **Dari Tanggal & Sampai Tanggal:** Rentang tanggal transaksi.
   - **Cabang:** Saring berdasarkan outlet cabang (Pusat, Sukun, Dinoyo).
   - **Metode Pembayaran:** Saring khusus transaksi Cash atau QRIS.
   - **Kode Transaksi:** Pencarian spesifik nomor struk (misal `TRX202610`).
   - **Nama Menu:** Mencari transaksi yang memuat menu tertentu.
3. Klik tombol **Filter** untuk menjalankan pencarian.

### B. Melihat Rincian & Mencetak Struk
1. Klik tombol ikon mata (biru) atau klik Kode Transaksi pada tabel.
2. Pada halaman **Detail Transaksi**:
   - Tampil rincian lengkap: nama cabang outlet, alamat cabang, waktu transaksi, nama kasir bertugas, metode pembayaran (Cash / QRIS), catatan referensi (RRN QRIS), uang diterima, dan kembalian.
   - Klik tombol **Cetak Struk** untuk langsung membuka jendela cetak printer thermal kasir / simpan PDF nota.
   - **Bukti Foto QRIS:** Pada panel samping kanan, tampil foto bukti pembayaran yang telah diambil kasir. Klik foto atau tombol **Perbesar Foto** untuk membuka lightbox modal beresolusi tinggi, atau klik **Unduh** untuk menyimpan file.
   - Pada tabel **Riwayat Transaksi**, transaksi QRIS yang memiliki foto bukti akan menampilkan lencana ikon kamera biru (📷) yang dapat langsung diklik untuk melihat foto.

### C. Mengedit & Menghapus Transaksi *(Khusus Admin)*
- **Edit Transaksi:** Klik tombol **Edit** ➔ Keranjang pesanan lama akan dimuat ulang ke layar POS beserta cabang dan metode pembayarannya ➔ Sesuaikan item/qty/diskon/metode bayar ➔ Klik **Update Transaksi**.
- **Hapus Transaksi:** Klik tombol **Hapus Transaksi** ➔ Konfirmasi dialog SweetAlert2 ➔ Transaksi dan itemnya akan dihapus permanen dari database.

---

## 5. Kas & Belanja Harian Warung (Petty Cash & Tabungan)
**Menu Sidebar:** `Transaksi` ➔ `Kas & Belanja Harian` (`/operational-cash`)

Fitur pencatatan kas harian kasir untuk belanja kebutuhan operasional warung (seperti beras, gas LPG, minyak, kantong kresek, teh/gula, sabun cuci) serta pencatatan setoran tabungan hasil penjualan harian.

### A. Peran & Alur Kasir:
1. **Belanja Barang Kebutuhan Warung (Pengeluaran Kas):**
   - Kasir membeli barang kebutuhan harian warung menggunakan kas harian.
   - Buka menu **Kas & Belanja Harian** ➔ Klik tombol **+ Catat Belanja / Tabungan** (`/operational-cash/create`).
   - Tersedia tombol cepat (*Quick Chips*) untuk kebutuhan rutin:
     - 🍚 **Beras** (Kategori: Bahan Baku)
     - 🔥 **Gas LPG 3kg** (Kategori: Gas & Energi)
     - 🛍️ **Kantong Kresek** (Kategori: Kemasan & Plastik)
     - 🛢️ **Minyak Goreng** (Kategori: Bahan Baku)
     - 🍵 **Gula & Teh** (Kategori: Bahan Baku)
     - 🧼 **Sabun Cuci** (Kategori: Kebersihan & Sanitasi)
   - Masukkan nominal harga pembelian (Rp) dan catatan tempat belanja / nomor nota warung (opsional).
   - Klik **Simpan Catatan Kas**.

2. **Menabung Kas / Setoran Harian:**
   - Kasir menyisihkan sebagian uang cash hasil penjualan harian untuk ditabung / disetorkan ke owner.
   - Pada formulir kas, pilih jenis **Menabung Kas** (atau klik tombol cepat 💰 **Tabungan Harian**).
   - Masukkan nominal uang yang ditabung (misal: Rp 250.000).
   - Klik **Simpan Catatan Kas**.

3. **Isolasi Data Cabang Kasir:**
   - Kasir otomatis mencatat dan melihat pengeluaran serta tabungan di cabangnya masing-masing.
   - Owner dan Admin dapat memonitor seluruh pengeluaran belanja dan tabungan di semua cabang secara real-time.

---

## 6. Master Menu & Master Kategori

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

## 7. Analisis FP-Growth (Market Basket Analysis)
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

## 8. Laporan Penjualan & Ekspor (Excel/PDF)
**Menu Sidebar:** `Analisis & Laporan` ➔ `Laporan Penjualan` (`/reports`)

Digunakan oleh Owner dan Admin untuk rekapitulasi data akuntansi & penjualan berkala dengan pemisahan per cabang dan metode pembayaran.

### Cara Menggunakan:
1. **Filter Laporan:**
   - Tentukan **Dari Tanggal** & **Sampai Tanggal**.
   - **Filter Cabang:** Pilih *Kobana Pusat*, *Kobana Sukun*, *Kobana Dinoyo*, atau *Semua Cabang*.
   - **Filter Metode Bayar:** Pilih *Cash (Tunai)*, *QRIS*, atau *Semua Metode*.
   - **Filter Kategori:** Pilih kategori menu tertentu jika diperlukan.
   - Klik **Filter**.
2. **Melihat Ringkasan:**
   - Bagian atas menampilkan ringkasan: Total Omzet, Total Transaksi, Item Terjual, dan Rata-rata Belanja sesuai filter cabang dan metode bayar.
   - Tabel **Laporan Harian:** Rincian transaksi dan omzet per hari kalender.
   - Tabel **Laporan per Menu:** Rincian kuantitas terjual dan subtotal omzet per masing-masing menu.
3. **Ekspor Data:**
   - **Ekspor CSV / Excel:** Klik tombol hijau **Ekspor CSV / Excel** untuk mengunduh spreadsheet `.csv` (dilengkapi kolom nama Cabang dan Metode Pembayaran, serta UTF-8 BOM agar rapi di Microsoft Excel).
   - **Ekspor PDF (Print):** Klik tombol merah **Ekspor PDF (Print)** untuk mencetak laporan resmi yang mencantumkan nama cabang dan metode pembayaran yang disaring.

---

## 9. Impor Dataset Penjualan (Excel)
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

## 10. Manajemen Pengguna (User) & Profil
**Menu Sidebar:** `Sistem` ➔ `Manajemen User` (`/users`) *(Khusus Admin)*

### A. Menambah & Menugaskan Kasir ke Cabang:
1. Klik **+ Tambah Pengguna**.
2. Masukkan **Nama Lengkap**, **Alamat Email**, **Password** (minimal 6 karakter).
3. Pilih **Peran (Role)**:
   - `Admin`: Akses penuh ke seluruh sistem dan seluruh cabang.
   - `Kasir`: Fokus pada operasional kasir POS & riwayat cabang.
   - `Owner`: Pemilik dengan akses monitoring dashboard, analisis data FP-Growth, dan laporan seluruh cabang.
4. **Penugasan Cabang:**
   - Pada dropdown **Penugasan Cabang**, pilih cabang tempat kasir bertugas (*Kobana Pusat*, *Kobana Sukun*, atau *Kobana Dinoyo*).
   - Kasir yang sudah ditugaskan ke cabang tertentu otomatis akan mencatat setiap transaksi ke cabang tersebut tanpa perlu memilih cabang secara manual.
5. Centang **Status Aktif** lalu klik **Simpan Pengguna**.

### B. Mengubah Status & Hapus Pengguna:
- Gunakan filter **Peran (Role)** dan **Cabang** di halaman daftar pengguna untuk mempermudah pencarian staf per outlet.
- Klik tombol mata/gembok untuk mengaktifkan atau menonaktifkan akun karyawan yang sudah tidak bekerja.
- Sistem memiliki **Proteksi Akun Sendiri** (Anda tidak dapat menonaktifkan atau menghapus akun Anda sendiri saat sedang login).

### C. Ganti Password Pribadi:
1. Klik foto profil / nama Anda di pojok kanan atas navbar ➔ Pilih **Ganti Password** (`/profile/password`).
2. Masukkan password saat ini dan password baru minimal 6 karakter.
3. Klik **Perbarui Password**.

---

## 11. Tips & Troubleshooting

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
