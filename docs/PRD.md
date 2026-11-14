# 📄 Product Requirements Document (PRD)
## Sistem Manajemen POS Multi-Cabang & Analisis Keranjang Belanja FP-Growth
### Kobana Fried Chicken

---

## 1. Informasi Dokumen & Riwayat Versi

- **Nama Proyek:** Kobana POS & Market Basket Analysis Engine
- **Versi:** 2.1.0
- **Status:** *Production Ready*
- **Target Pengguna:** Kasir Outlet, Store Supervisor / Admin, dan Owner Bisnis
- **Terakhir Diperbarui:** November 2026

---

## 2. Latar Belakang & Pernyataan Masalah

### 2.1 Latar Belakang
Kobana Fried Chicken adalah usaha kuliner cepat saji yang terus berkembang pesat dan saat ini telah beroperasi di 3 cabang strategis (Kobana Pusat, Kobana Sukun, dan Kobana Dinoyo). Transaksi harian mencakup ribuan pesanan menu ayam krispi, paket nasi, aneka sambal, serta minuman pendukung. Seiring bertambahnya cabang dan volume penjualan, pemilik usaha menghadapi tantangan dalam:
1. Menjaga standarisasi dan kecepatan kasir saat melayani pesanan di meja kasir.
2. Mengakomodasi tren pembayaran non-tunai (khususnya QRIS) dengan verifikasi bukti transaksi yang valid dan dapat dipertanggungjawabkan tanpa manipulasi.
3. Mengontrol perputaran kas kecil (*petty cash*) harian kasir untuk pembelian bahan baku darurat (beras, minyak goreng, gas LPG, kantong kresek, bumbu dapur) serta setoran tabungan hasil penjualan harian dari tiap warung cabang.
4. Memanfaatkan tumpukan data transaksi ribuan baris untuk menemukan pola kombinasi menu yang sering dibeli bersamaan (*Market Basket Analysis*) guna merancang strategi *bundling* promosi menu berbasis data ilmiah.

### 2.2 Pernyataan Masalah (*Problem Statement*)
- Sistem pencatatan kasir manual rawan salah hitung kembalian dan lambat saat jam sibuk (*peak hours*).
- Pembayaran QRIS kerap berisiko terjadi kecurangan (*fraud*) jika kasir tidak mendokumentasikan bukti bayar secara instan ke dalam sistem nota transaksi.
- Pembelian harian kebutuhan warung sering kali tercampur dengan kas penjualan utama tanpa rekonsiliasi yang jelas per cabang.
- Pemilik usaha (*Owner*) kesulitan memantau perbandingan performa antar 3 cabang secara *real-time* dari satu dasbor terpusat.
- Tidak adanya modul data mining otomatis untuk menentukan menu yang cocok dipaketkan bersamaan dengan algoritma FP-Growth.

---

## 3. Profil Pengguna (*User Personas*) & Matriks Hak Akses

### 3.1 Persona Pengguna
1. **Kasir Cabang (*Cashier*)**:
   - Karakteristik: Mengutamakan kecepatan antarmuka, minim klik, tombol responsif di layar sentuh/laptop kasir, kalkulasi otomatis uang pas/kembalian, dan fitur kamera instan untuk foto bukti QRIS pelanggan.
   - Kebutuhan: Input pesanan cepat, pencatatan belanja barang harian warung, cetak struk nota printer thermal.
2. **Administrator (*Store Manager / Admin*)**:
   - Karakteristik: Memerlukan kontrol penuh data master, perbaikan transaksi jika terjadi revisi kasir, pengelolaan data pengguna/karyawan, dan audit laporan.
   - Kebutuhan: Manajemen menu, kategori, akun kasir per cabang, rekonsiliasi transaksi, impor massal dataset Excel.
3. **Pemilik Bisnis (*Owner*)**:
   - Karakteristik: Berorientasi pada metrik bisnis, laba kotor, perbandingan tren penjualan antar cabang, dan keputusan strategis bundling menu.
   - Kebutuhan: Dasbor analitik eksekutif, filter kinerja per cabang, ekspor laporan penjualan PDF/Excel, dan eksekusi algoritma data mining FP-Growth.

### 3.2 Matriks Hak Akses (*Role-Based Access Control*)

| Fitur / Modul Sistem | Kasir Cabang | Administrator | Owner |
|---|:---:|:---:|:---:|
| **Login / Logout & Ganti Password** | ✅ | ✅ | ✅ |
| **Kasir / Input Transaksi Baru (POS)** | ✅ (Terkunci ke Cabang) | ✅ (Bisa Pilih Cabang) | ❌ |
| **Kamera Foto Bukti Pembayaran QRIS** | ✅ | ✅ | ❌ |
| **Riwayat & Cetak Struk Transaksi** | ✅ (Cabang Sendiri) | ✅ (Semua Cabang) | ✅ (Semua Cabang) |
| **Edit & Hapus Transaksi Lama** | ❌ | ✅ | ❌ |
| **Kas & Belanja Harian Warung** | ✅ (Input & Cabang Sendiri) | ✅ (Semua Cabang) | ✅ (Monitoring Semua) |
| **Master Data Menu & Kategori** | 👁️ (Hanya Lihat) | ✅ (Full CRUD) | 👁️ (Hanya Lihat) |
| **Dashboard Analitik Eksekutif** | 👁️ (Cabang Sendiri) | ✅ (Filter Semua Cabang) | ✅ (Filter Semua Cabang) |
| **Analisis Pola Belanja (FP-Growth)** | ❌ | ✅ (Jalankan & Hapus) | ✅ (Jalankan & Lihat) |
| **Laporan Penjualan (Excel & PDF)** | ❌ | ✅ | ✅ |
| **Impor Dataset Penjualan (Excel)** | ❌ | ✅ | ❌ |
| **Manajemen Pengguna & Penugasan Cabang** | ❌ | ✅ | ❌ |

---

## 4. Kebutuhan Fungsional (*Functional Requirements*)

### FR-01: Manajemen Multi-Cabang & Otorisasi Pengguna
- **FR-01.1:** Sistem wajib mendukung isolasi data cabang untuk 3 outlet resmi: Kobana Pusat (`KBN01`), Kobana Sukun (`KBN02`), dan Kobana Dinoyo (`KBN03`).
- **FR-01.2:** Pengguna kasir yang login otomatis terikat pada `branch_id` penugasannya; sistem wajib memfilter transaksi dan kas operasional sesuai cabang kasir tersebut.
- **FR-01.3:** Admin dan Owner memiliki visibilitas global dengan opsi dropdown pemilihan cabang atau *Semua Cabang*.
- **FR-01.4:** Sistem menyimpan kata sandi dengan enkripsi bcrypt yang aman dan menyediakan fitur ganti kata sandi mandiri.

### FR-02: Master Menu & Taksonomi Kategori
- **FR-02.1:** Sistem mendukung CRUD data menu lengkap dengan kode unik (`M01`..`M19`), nama menu, relasi kategori, harga jual (dalam Rupiah tanpa desimal), serta status aktif (`is_active`).
- **FR-02.2:** Mendukung *Soft Deletes* pada menu agar riwayat transaksi masa lalu tidak rusak jika sebuah menu dihapus dari peredaran.
- **FR-02.3:** CRUD kategori makanan/minuman dengan penegakan integritas data unik.

### FR-03: Antarmuka Point of Sale (POS) Kasir
- **FR-03.1:** Pencarian menu instan secara asinkron berdasarkan kode atau nama produk di layar POS.
- **FR-03.2:** Keranjang belanja dinamis yang memungkinkan penambahan kuantitas, pengurangan, penghapusan baris, pengosongan keranjang, serta pemberian diskon per baris item.
- **FR-03.3:** Pemberian kode transaksi otomatis dan terurut dengan format `TRX` + `YYYYMMDD` + 5 digit urutan unik (contoh: `TRX2026111400001`).
- **FR-03.4:** Penegakan *atomic database transaction* dan penguncian baris sekuensial (*Row-Level Database Locking*) untuk menjamin tidak terjadi duplikasi nomor struk saat kasir bertransaksi bersamaan.

### FR-04: Sistem Pembayaran & Kamera Bukti QRIS
- **FR-04.1:** Dukungan pembayaran **Cash (Tunai)**:
  - Kotak isian nominal uang diterima dengan tombol pintas cepat (*Uang Pas, 20rb, 50rb, 100rb*).
  - Perhitungan otomatis dan tampilan nilai kembalian real-time. Jika uang kurang, tombol simpan dicegah dan muncul indikator merah.
- **FR-04.2:** Dukungan pembayaran **QRIS**:
  - Kolom opsional nomor referensi struk / RRN (*Retrieval Reference Number*).
  - **Fitur Kamera Pengambil Bukti**: Antarmuka langsung mengakses webcam laptop/PC kasir atau kamera belakang perangkat seluler/tablet menggunakan API browser native (`navigator.mediaDevices.getUserMedia`).
  - Pengambilan foto langsung (*snapshot*) ke kanvas HTML5 dan diunggah secara otomatis dalam bentuk file kompresi JPEG.
  - Alternatif tombol **Unggah File** untuk melampirkan file tangkapan layar (*screenshot*) bukti pembayaran jika bukti dikirim via perpesanan instan.
  - Kartu pratinjau foto instan (*Foto Siap*) dengan tombol hapus/ambil ulang.
- **FR-04.3:** Halaman detail nota transaksi menyajikan kartu *Bukti Pembayaran QRIS* lengkap dengan tombol pembuka modal resolusi tinggi (*Lightbox Modal*) dan tautan unduh.
- **FR-04.4:** Tabel riwayat transaksi menampilkan lencana kamera biru (`📷`) yang dapat diklik langsung untuk memverifikasi bukti.

### FR-05: Kas & Belanja Harian Warung (*Petty Cash & Tabungan*)
- **FR-05.1:** Pencatatan arus kas kecil harian kasir per cabang dengan dua jenis arus:
  - **Pengeluaran**: Belanja kebutuhan operasional (beras, minyak goreng, gas LPG, kantong kresek, teh/gula, sabun, telur, dll).
  - **Pemasukan**: Setoran kas / tabungan hasil penjualan harian warung.
- **FR-05.2:** Ringkasan kartu saldo per cabang: Total Pemasukan, Total Pengeluaran, dan Saldo Akhir Kas Warung.
- **FR-05.3:** Validasi saldo: Kasir dicegah mencatat pengeluaran yang melebihi saldo kas yang tersedia di cabang tersebut.
- **FR-05.4:** Riwayat transaksi kas harian dilengkapi filter tanggal, jenis transaksi, cabang, dan fungsi cetak nota pengeluaran kas kecil.

### FR-06: Analisis Pola Belanja Keranjang (*FP-Growth Data Mining Engine*)
- **FR-06.1:** Kemampuan menjalankan algoritma **FP-Growth (Frequent Pattern Growth)** murni tanpa dependensi library eksternal Python:
  - Membangun struktur pohon pola frekuensi (*FP-Tree*) dan tabel header terurut.
  - Menghasilkan kumpulan item yang sering dibeli bersamaan (*Frequent Itemsets*) berdasarkan nilai ambang batas *Minimum Support* (0.01 – 1.0).
  - Menghasilkan aturan asosiasi (*Association Rules*) berdasarkan nilai ambang batas *Minimum Confidence* (0.1 – 1.0).
  - Menghitung metrik korelasi *Lift Ratio* untuk menguji signifikansi aturan asosiasi ($Lift > 1$ mengindikasikan hubungan positif yang kuat).
- **FR-06.2:** Eksekusi latar belakang melalui antrean pekerjaan (*Laravel Queue Worker*) agar proses data mining ribuan transaksi tidak memblokir antarmuka pengguna web.
- **FR-06.3:** Penyimpanan riwayat hasil analisis dengan status (*pending, running, done, failed*) beserta durasi komputasi dan jumlah transaksi yang dianalisis.
- **FR-06.4:** Rekomendasi bisnis otomatis berbasis aturan asosiasi tertinggi untuk panduan pembuatan paket promo bundling outlet.

### FR-07: Dashboard Analitik Eksekutif
- **FR-07.1:** Kartu ringkasan metrik utama: Total Omzet Penjualan, Total Transaksi, Total Item Terjual, dan Nilai Rata-rata Belanja (*Average Order Value*).
- **FR-07.2:** Kartu rincian kontribusi metode pembayaran: Total Omzet Cash vs Total Omzet QRIS beserta rasio persentasenya.
- **FR-07.3:** Visualisasi grafik interaktif (*Chart.js*):
  - Grafik tren omzet dan jumlah transaksi harian (Kombinasi Line & Bar Chart).
  - Grafik distribusi penjualan per jam (*Peak Hours Operational Analysis*).
  - Grafik perbandingan omzet hari dalam seminggu (*Day of Week Analysis*).
  - Grafik 10 Menu Terlaris (*Top 10 Best Selling Items*).
  - Grafik donat komposisi omzet per kategori menu.
- **FR-07.4:** Filter dinamis berdasarkan rentang tanggal dan cabang toko.

### FR-08: Laporan & Ekspor Data Keuangan
- **FR-08.1:** Halaman laporan penjualan dengan penyaringan komprehensif berdasarkan rentang tanggal, cabang, kasir, dan metode pembayaran.
- **FR-08.2:** Fitur ekspor laporan ke format **Microsoft Excel (`.xlsx`)** menggunakan library spreadsheet profesional.
- **FR-08.3:** Fitur ekspor laporan ke format dokumen cetak resmi **PDF (`.pdf`)** lengkap dengan kop surat Kobana Fried Chicken, rincian omzet, dan tanda tangan penanggung jawab.

### FR-09: Impor Dataset Penjualan Massal
- **FR-09.1:** Fitur unggah file Excel dataset transaksi lampau (`kobana_dataset.xlsx`).
- **FR-09.2:** Pembacaan lembar kerja multi-sheet (*Master_Menu, Header_Transaksi, Detail_Transaksi*).
- **FR-09.3:** Validasi data otomatis dan pencatatan log impor (*import_logs*) dengan penanganan kesalahan per baris tanpa menghentikan proses keseluruhan.

---

## 5. Kebutuhan Non-Fungsional (*Non-Functional Requirements*)

### NFR-01: Kinerja & Waktu Respon (*Performance*)
- Waktu respon penyimpanan transaksi POS di bawah 500 milidetik pada kondisi jaringan normal.
- Pemuatan halaman web di bawah 1,5 detik dengan memanfaatkan aset CDN terkompresi (Bootstrap, Chart.js, SweetAlert2) tanpa proses kompilasi aset lokal yang berat.
- Operasi komputasi berat (FP-Growth dan Impor Excel > 5.000 baris) dieksekusi secara asinkron melalui sistem antrean (*Queue Database Driver*).

### NFR-02: Keamanan Sistem (*Security*)
- **Perlindungan CSRF:** Seluruh request form metode POST, PUT, dan DELETE wajib menyertakan token CSRF Laravel yang valid.
- **Pencegahan SQL Injection:** Seluruh kueri basis data dibangun menggunakan Eloquent ORM atau PDO Parameter Binding yang aman.
- **Perlindungan Mass Assignment:** Semua model Eloquent mendefinisikan array `$fillable` secara ketat.
- **Validasi File Unggahan:** File bukti transaksi QRIS dan struk kas divalidasi tipe MIME (gambar JPEG, PNG, WEBP) dengan batasan ukuran maksimum 5MB.
- **Sanitasi Base64:** Input foto kamera webcam divalidasi struktur header base64 (`data:image/(jpeg|png);base64,...`) sebelum ditulis ke media penyimpanan fisik.

### NFR-03: Ketersediaan & Keandalan (*Reliability & Compatibility*)
- Aplikasi dapat berjalan pada berbagai ukuran layar (*responsive web design*) dari layar monitor kasir, laptop, tablet, hingga smartphone kasir.
- Fitur kamera kasir berjalan pada seluruh peramban modern yang mengimplementasikan standar HTML5 W3C Media Capture API (Google Chrome, Microsoft Edge, Mozilla Firefox, Safari Mobile). Jika perangkat tidak memiliki kamera atau izin akses ditolak, sistem menyediakan fallback otomatis tombol unggah file manual.

### NFR-04: Integritas Data & Transaksi (*Data Integrity*)
- Operasi penyimpanan transaksi POS dan detail item pesanan dibungkus dalam `DB::transaction()` atomik; jika terjadi kegagalan sistem di tengah jalan, seluruh perubahan di-rollback secara otomatis.
- Relasi integritas database menerapkan *Foreign Key Constraints* dengan strategi penghapusan kaskade yang terkontrol atau pembatasan hapus (*restrict*).

---

## 6. Aturan Bisnis & Formula Perhitungan

### 6.1 Formula Transaksi Kasir
- **Subtotal Item:**
  $$\text{Subtotal} = (\text{Harga Menu} \times \text{Kuantitas}) - \text{Diskon}$$
  *Batasan: Nilai diskon tidak boleh melebihi $(\text{Harga Menu} \times \text{Kuantitas})$, sehingga subtotal minimal adalah Rp 0.*
- **Total Pembayaran Transaksi:**
  $$\text{Total Bayar} = \sum_{i=1}^{n} \text{Subtotal}_i$$
- **Nominal Kembalian (Khusus Pembayaran Tunai):**
  $$\text{Kembalian} = \max(0, \text{Uang Tunai Diterima} - \text{Total Bayar})$$

### 6.2 Formula Data Mining FP-Growth
Diberikan basis data transaksi $D$ dengan total $|D|$ transaksi:
- **Support Itemset ($X$):**
  $$Support(X) = \frac{\text{Frekuensi Kemunculan Itemset } X}{|D|}$$
- **Confidence Aturan Asosiasi ($X \rightarrow Y$):**
  $$Confidence(X \rightarrow Y) = \frac{Support(X \cup Y)}{Support(X)}$$
- **Lift Ratio Aturan Asosiasi ($X \rightarrow Y$):**
  $$Lift(X \rightarrow Y) = \frac{Confidence(X \rightarrow Y)}{Support(Y)} = \frac{Support(X \cup Y)}{Support(X) \times Support(Y)}$$
  - Nilai $Lift > 1$ membuktikan bahwa pembelian menu $X$ secara signifikan meningkatkan probabilitas pembelian menu $Y$.

---

## 7. Metrik Keberhasilan Produk (*Key Performance Indicators*)

1. **Waktu Pelayanan Kasir (*Checkout Speed*)**: Penurunan durasi pencatatan satu nota transaksi menjadi rata-rata di bawah 30 detik.
2. **Akurasi & Integritas Bukti Pembayaran**: 100% transaksi non-tunai (QRIS) memiliki lampiran foto bukti fisik atau nomor referensi RRN yang valid.
3. **Rekonsiliasi Kas Harian Warung**: Selisih pencatatan uang kas kecil harian dan sisa belanja bahan baku warung mencapai 0% deviasi per akhir shift kasir.
4. **Adopsi Paket Bundling**: Minimum 1 rekomendasi paket menu hasil analisis FP-Growth diterapkan sebagai strategi promosi bulanan cabang.
