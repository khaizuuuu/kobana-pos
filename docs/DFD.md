# 🔄 Data Flow Diagram (DFD)
## Diagram Aliran Data Sistem POS & Analisis Keranjang Belanja FP-Growth
### Kobana Fried Chicken

---

## 1. Notasi & Entitas Sistem

Diagram Aliran Data (*Data Flow Diagram* / DFD) memetakan transformasi data di dalam sistem, mulai dari input pengguna, proses bisnis, penyimpanan data (*data store*), hingga keluaran informasi (*output*).

### 1.1 Entitas Luar (*External Entities*)
1. **Kasir Cabang**: Petugas kasir di outlet fisik (Kobana Pusat, Sukun, Dinoyo) yang memasukkan pesanan, memproses pembayaran, mengambil foto bukti QRIS, dan mencatat kas belanja warung.
2. **Administrator**: Pengelola operasional toko yang mengelola master menu/kategori, hak akses user, impor dataset, dan perbaikan data transaksi.
3. **Pemilik Bisnis (*Owner*)**: Pimpinan eksekutif yang memantau performa penjualan seluruh cabang, membaca laporan, dan memanfaatkan analisis rekomendasi menu FP-Growth.
4. **Pelanggan (*Customer*)**: Konsumen yang melakukan pemesanan dan pembayaran (tunai atau kode QRIS).
5. **Kamera Perangkat Kasir (*Camera / Scanner Input*)**: Perangkat keras penangkap gambar (webcam PC atau kamera HP) untuk merekam bukti transaksi QRIS.
6. **Printer Thermal Kasir / Storage Berkas**: Perangkat cetak nota fisik dan sistem penyimpanan berkas bukti transaksi.

### 1.2 Penyimpanan Data (*Data Stores*)
- **D1: `users`** (Akun, Peran, & Penugasan Cabang)
- **D2: `branches`** (Identitas Outlet Cabang)
- **D3: `menus` & `categories`** (Katalog Produk & Kategori)
- **D4: `transactions` & `transaction_items`** (Arsip Penjualan POS)
- **D5: `operational_cashes`** (Arsip Kas Kecil & Tabungan Harian)
- **D6: `fp_analyses`, `fp_frequent_itemsets`, `fp_association_rules`** (Hasil Data Mining)
- **D7: `storage/app/public/`** (Berkas Fisik Foto Bukti Bayar & Struk)
- **D8: `import_logs`** (Catatan Jejak Audit Impor)

---

## 2. DFD Level 0 — Diagram Konteks (*Context Diagram*)

Diagram konteks menggambarkan sistem sebagai satu proses tunggal yang berinteraksi dengan entitas luar.

```mermaid
flowchart TD
    subgraph External_Entities [Entitas Luar]
        KASIR["Kasir Cabang"]
        ADMIN["Administrator"]
        OWNER["Pemilik Bisnis (Owner)"]
        KAMERA["Kamera Perangkat Kasir"]
        PRINTER["Printer Thermal Kasir"]
    end

    SYSTEM(("0.0<br/>Sistem Informasi POS &<br/>Analisis FP-Growth Kobana"))

    %% Aliran Kasir
    KASIR -- "Kredensial Login" --> SYSTEM
    KASIR -- "Data Pesanan Menu & Diskon" --> SYSTEM
    KASIR -- "Nominal Tunai / Ref QRIS" --> SYSTEM
    KASIR -- "Catatan Kas Belanja Warung & Tabungan" --> SYSTEM
    SYSTEM -- "Info Stok, Harga & Kembalian" --> KASIR
    SYSTEM -- "Struk Nota Penjualan" --> KASIR
    SYSTEM -- "Rekap Saldo Kas Harian" --> KASIR

    %% Aliran Kamera
    KAMERA -- "Stream Gambar / Foto Bukti QRIS" --> SYSTEM

    %% Aliran Printer
    SYSTEM -- "Perintah Cetak Struk POS & Kas" --> PRINTER

    %% Aliran Admin
    ADMIN -- "Kredensial Login Admin" --> SYSTEM
    ADMIN -- "CRUD Menu & Kategori" --> SYSTEM
    ADMIN -- "CRUD Pengguna & Penugasan Cabang" --> SYSTEM
    ADMIN -- "Berkas Excel Dataset Penjualan" --> SYSTEM
    ADMIN -- "Koreksi Data Transaksi" --> SYSTEM
    SYSTEM -- "Status Impor & Peringatan Baris" --> ADMIN
    SYSTEM -- "Daftar Audit Transaksi Semua Cabang" --> ADMIN

    %% Aliran Owner
    OWNER -- "Kredensial Login Owner" --> SYSTEM
    OWNER -- "Filter Parameter Dasbor (Tanggal & Cabang)" --> SYSTEM
    OWNER -- "Parameter FP-Growth (Min Support & Confidence)" --> SYSTEM
    SYSTEM -- "Grafik & Metrik Omzet Multi-Cabang" --> OWNER
    SYSTEM -- "Laporan Penjualan (Excel & PDF)" --> OWNER
    SYSTEM -- "Pola Aturan Asosiasi Menu (Rekomendasi Bundling)" --> OWNER
```

---

## 3. DFD Level 1 — Dekomposisi Sistem Utama

DFD Level 1 menguraikan sistem menjadi 7 sub-proses utama dan interaksinya dengan *Data Store*.

```mermaid
flowchart TD
    subgraph P1 [Proses Utama]
        P10["1.0<br/>Autentikasi & Otorisasi Pengguna"]
        P20["2.0<br/>Pengelolaan Master Menu & Kategori"]
        P30["3.0<br/>Pemrosesan Transaksi POS & Pembayaran"]
        P40["4.0<br/>Pencatatan Kas & Belanja Harian Warung"]
        P50["5.0<br/>Komputasi Pola Belanja FP-Growth"]
        P60["6.0<br/>Pelaporan & Dasbor Analitik Eksekutif"]
        P70["7.0<br/>Pemrosesan Impor Dataset Excel"]
    end

    subgraph Stores [Penyimpanan Data]
        D1[("D1: Users")]
        D2[("D2: Branches")]
        D3[("D3: Menus & Categories")]
        D4[("D4: Transactions & Items")]
        D5[("D5: Operational Cashes")]
        D6[("D6: FP-Growth Mining Results")]
        D7[("D7: Storage Foto Bukti")]
        D8[("D8: Import Logs")]
    end

    subgraph Actors [Aktor]
        AK_KASIR["Kasir Cabang"]
        AK_ADMIN["Administrator"]
        AK_OWNER["Owner"]
        AK_CAM["Kamera Kasir"]
        AK_PRINT["Printer / File System"]
    end

    %% Aliran P1.0
    AK_KASIR & AK_ADMIN & AK_OWNER -->|Email & Password| P10
    P10 <-->|Verifikasi Hash & Cabang| D1
    P10 <-->|Validasi Info Cabang| D2
    P10 -->|Sesi & Hak Akses Aktif| AK_KASIR & AK_ADMIN & AK_OWNER

    %% Aliran P2.0
    AK_ADMIN -->|Input Kode, Nama, Harga, Kategori| P20
    P20 <-->|Tulis / Baca Master Data| D3

    %% Aliran P3.0
    AK_KASIR -->|Pilih Menu, Qty, Diskon, Metode Bayar| P30
    D3 -->|Info Harga & Menu Aktif| P30
    AK_CAM -->|Snapshot / Upload Bukti QRIS| P30
    P30 -->|Simpan Foto Bukti| D7
    P30 -->|Simpan Header & Item Transaksi| D4
    P30 -->|Kirim Nota Cetak| AK_PRINT
    P30 -->|Tampilan Nota Selesai| AK_KASIR

    %% Aliran P4.0
    AK_KASIR -->|Catat Belanja Bahan / Setor Tabungan| P40
    D2 -->|Identitas Cabang| P40
    P40 <-->|Validasi Saldo & Catat Arus Kas| D5
    P40 -->|Simpan Foto Struk Fisik| D7
    P40 -->|Rekap Saldo Warung| AK_KASIR

    %% Aliran P5.0
    AK_OWNER & AK_ADMIN -->|Set Min Support & Confidence| P50
    D4 -->|Ekstraksi Dataset Transaksi| P50
    P50 -->|Simpan Frequent Itemsets & Rules| D6
    P50 -->|Visualisasi Aturan Asosiasi Bundling| AK_OWNER

    %% Aliran P6.0
    AK_OWNER & AK_ADMIN -->|Filter Tanggal & Pilihan Cabang| P60
    D4 -->|Agregasi Penjualan & Omzet| P60
    D5 -->|Agregasi Pengeluaran Kas| P60
    D2 -->|Daftar Cabang Aktif| P60
    P60 -->|Grafik Tren, Peak Hours, Donut| AK_OWNER & AK_ADMIN
    P60 -->|Ekspor File Excel / PDF| AK_PRINT

    %% Aliran P7.0
    AK_ADMIN -->|Upload File kobana_dataset.xlsx| P70
    P70 -->|Tulis Master Menu Baru| D3
    P70 -->|Tulis Transaksi Massal| D4
    P70 -->|Catat Log Status & Peringatan| D8
    P70 -->|Laporan Hasil Impor| AK_ADMIN
```

---

## 4. DFD Level 2 — Rincian Proses Kritis

### 4.1 Dekomposisi Proses 3.0: Pemrosesan Transaksi POS & Pembayaran QRIS Kamera

Diagram ini membedah alur detail saat kasir melayani transaksi di meja kasir.

```mermaid
flowchart TD
    subgraph P3_Subprocesses [Proses Rinci Transaksi POS]
        P31["3.1 Pencarian Menu & Tambah Item"]
        P32["3.2 Kalkulasi Keranjang & Diskon Baris"]
        P33["3.3 Pemilihan Metode Pembayaran (Cash/QRIS)"]
        P34["3.4 Penangkap Kamera & Upload Bukti QRIS"]
        P35["3.5 Penguncian Nomor Urut & Penyimpanan Atomik"]
        P36["3.6 Pembuatan Tampilan Nota & Cetak Struk"]
    end

    KASIR["Kasir Cabang"]
    KAMERA["Kamera Perangkat"]
    STORE_MENU[("D3: Menus")]
    STORE_SEQ[("D4: Sequences")]
    STORE_TRX[("D4: Transactions & Items")]
    STORE_IMG[("D7: Storage Proofs")]
    PRINTER["Printer Nota"]

    %% Langkah 3.1
    KASIR -->|"Kata Kunci Pencarian (Nama/Kode)"| P31
    STORE_MENU -->|"Daftar Menu Aktif & Harga"| P31
    P31 -->|"Item Menu Terpilih"| P32

    %% Langkah 3.2
    KASIR -->|"Input Qty & Diskon"| P32
    P32 -->|"Total Item, Subtotal, & Total Bayar"| P33

    %% Langkah 3.3
    KASIR -->|"Pilih Metode: Cash"| P33
    KASIR -->|"Input Nominal Tunai"| P33
    P33 -->|"Hitung Kembalian Real-time"| KASIR
    
    KASIR -->|"Pilih Metode: QRIS"| P33
    P33 -->|"Aktifkan UI Kamera"| P34

    %% Langkah 3.4
    KAMERA -->|"Stream Video Live"| P34
    KASIR -->|"Klik Ambil Foto / Unggah File"| P34
    P34 -->|"Simpan File Gambar (JPEG)"| STORE_IMG
    P34 -->|"Path Bukti Pembayaran"| P35

    %% Langkah 3.5
    KASIR -->|"Konfirmasi Simpan Transaksi"| P35
    P35 <-->|"Locking & Ambil Next Seq"| STORE_SEQ
    P35 -->|"Simpan Header & Item (Atomic DB Trx)"| STORE_TRX
    P35 -->|"Objek Transaksi Sukses"| P36

    %% Langkah 3.6
    P36 -->|"Tampilan Rincian Nota & Bukti QRIS"| KASIR
    P36 -->|"Format Dokumen Cetak Thermal"| PRINTER
```

---

### 4.2 Dekomposisi Proses 4.0: Pencatatan Kas & Belanja Harian Warung

Menjelaskan alur pencatatan kas kecil belanja kebutuhan operasional (beras, minyak, gas LPG, plastik) dan setoran tabungan harian.

```mermaid
flowchart TD
    subgraph P4_Subprocesses [Proses Rinci Kas Warung]
        P41["4.1 Penerimaan Formulir Kas Harian"]
        P42["4.2 Validasi Kecukupan Saldo Cabang"]
        P43["4.3 Pemrosesan Foto Struk Fisik Belanja"]
        P44["4.4 Pencatatan Transaksi Kas Kecil"]
        P45["4.5 Rekalkulasi Buku Kas & Saldo Cabang"]
    end

    KASIR["Kasir Cabang"]
    STORE_CASH[("D5: Operational Cashes")]
    STORE_IMG[("D7: Storage Struk")]
    PRINTER["Printer Nota Kas"]

    KASIR -->|"Jenis (Pengeluaran/Pemasukan), Nominal, Ket"| P41
    P41 -->|"Cek Saldo untuk Pengeluaran"| P42
    STORE_CASH -->|"Total Masuk vs Total Keluar Saat Ini"| P42
    
    P42 -- "Saldo Tidak Cukup" -->|"Peringatan: Saldo Kas Kurang"| KASIR
    P42 -- "Saldo Cukup / Pemasukan" --> P43

    KASIR -->|"Foto Struk Nota Pembelian (Opsional)"| P43
    P43 -->|"Tulis File Struk"| STORE_IMG
    P43 -->|"Data Valid & Path Struk"| P44

    P44 -->|"Simpan Record Kas (KAS-YYYYMMDD-XXXX)"| STORE_CASH
    P44 --> P45

    STORE_CASH -->|"Agregasi Seluruh Transaksi Hari Ini"| P45
    P45 -->|"Kartu Saldo Masuk, Keluar & Sisa Kas"| KASIR
    P45 -->|"Cetak Tanda Terima Kas Kecil"| PRINTER
```

---

### 4.3 Dekomposisi Proses 5.0: Pipeline Algoritma FP-Growth

Menggambarkan tahapan analisis asosiasi menu keranjang belanja.

```mermaid
flowchart TD
    subgraph P5_Subprocesses [Pipeline Data Mining FP-Growth]
        P51["5.1 Ekstraksi & Filter Transaksi Penjualan"]
        P52["5.2 Hitung Support 1-Itemset & Eliminasi"]
        P53["5.3 Pembangunan FP-Tree & Header Table"]
        P54["5.4 Penambangan FP-Tree Bersyarat (Mining)"]
        P55["5.5 Pembangkitan Aturan Asosiasi (Rules)"]
        P56["5.6 Penyajian Rekomendasi Menu Bundling"]
    end

    OWNER["Pemilik / Admin"]
    STORE_TRX[("D4: Transactions & Items")]
    STORE_FP[("D6: FP-Growth Mining Results")]

    OWNER -->|"Input Tanggal, Min Support, Min Confidence"| P51
    STORE_TRX -->|"Daftar Transaksi & Menu ID"| P51
    P51 -->|"Daftar Keranjang Belanja Bersih"| P52

    P52 -->|"Item Lolos Ambang Batas Min Support"| P53
    P53 -->|"Pohon Pola Terkompresi (FP-Tree)"| P54
    
    P54 -->|"Daftar Frequent Itemsets (1, 2, 3.. item)"| P55
    P54 -->|"Simpan Itemset Frekuensi Tinggi"| STORE_FP

    P55 -->|"Kalkulasi Confidence & Lift Ratio"| P55
    P55 -->|"Simpan Association Rules (Lift > 1)"| STORE_FP

    STORE_FP -->|"Visualisasi Tabel Aturan & Rekomendasi"| P56
    P56 -->|"Insight Paket Promo Bundling Menu"| OWNER
```
