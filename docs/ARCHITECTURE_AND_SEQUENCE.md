# 🏗️ Arsitektur Sistem & Diagram Sekuensial
## Desain Perangkat Lunak & Alur Interaksi Komponen
### Kobana Fried Chicken

---

## 1. Arsitektur Perangkat Lunak Tingkat Tinggi (*High-Level Architecture*)

Sistem Kobana POS dirancang menggunakan arsitektur berlapis (*Layered Architecture*) berbasis pola **Model-View-Controller (MVC)** dari Laravel 13, dipadukan dengan pemrosesan antrean asinkron (*Background Queue Processing*) dan akses perangkat keras antarmuka browser bawaan (*Native HTML5 Hardware APIs*).

```mermaid
flowchart TD
    subgraph Presentation_Layer [Lapisan Presentasi (Client-Side)]
        BROWSER["Peramban Web (Desktop / Laptop / Mobile / Tablet)"]
        UI_FRAMEWORK["Bootstrap 5.3.3 + Icons (CDN - No Build Step)"]
        UI_NOTIF["SweetAlert2 (CDN Toast & Dialog Konfirmasi)"]
        UI_CHART["Chart.js 4 (CDN Grafik Interaktif Dashboard)"]
        UI_CAMERA["HTML5 MediaDevices API & Canvas (Akses Kamera Langsung)"]
    end

    subgraph Application_Layer [Lapisan Aplikasi & Kontroler (Server-Side)]
        ROUTER["Laravel Routing (web.php)"]
        MIDDLEWARE["Middleware (Auth, RoleMiddleware: admin|kasir|owner)"]
        
        subgraph Controllers [Controllers]
            C_AUTH["AuthController"]
            C_POS["TransactionController"]
            C_CASH["OperationalCashController"]
            C_DASH["DashboardController"]
            C_MENU["MenuController & CategoryController"]
            C_USER["UserController & BranchController"]
            C_REPORT["ReportController"]
            C_FP["FpGrowthController"]
            C_IMPORT["ImportController"]
        end
    end

    subgraph Business_Service_Layer [Lapisan Logika Bisnis & Servis]
        S_FP["FpGrowthService (FP-Tree Construction & Rule Mining)"]
        S_IMPORT["DatasetImportService (Spreadsheet Parser & Validator)"]
        S_EXPORT["ReportExportService (Excel / DomPDF Exporter)"]
    end

    subgraph Background_Queue_Layer [Lapisan Antrean Latar Belakang]
        Q_DB["Database Queue Driver (Tabel: jobs, failed_jobs)"]
        JOB_FP["RunFpGrowthJob"]
        JOB_IMPORT["ProcessDatasetImportJob"]
    end

    subgraph Persistence_Layer [Lapisan Persistensi & Data]
        ORM["Eloquent ORM (Models & Relationships)"]
        DB[("Relational Database (MySQL 8 / MariaDB / SQLite)")]
        STORAGE[("Local Public Storage (storage/app/public/payment_proofs/)")]
    end

    %% Relasi Aliran
    BROWSER --> ROUTER
    UI_CAMERA -.->|Stream Gambar Base64 / File| C_POS
    ROUTER --> MIDDLEWARE
    MIDDLEWARE --> Controllers

    C_POS --> ORM
    C_POS --> STORAGE
    C_CASH --> ORM
    C_CASH --> STORAGE
    C_DASH --> ORM
    C_MENU --> ORM
    C_USER --> ORM
    C_REPORT --> S_EXPORT
    
    C_FP --> JOB_FP
    C_IMPORT --> JOB_IMPORT
    
    JOB_FP --> Q_DB
    JOB_IMPORT --> Q_DB
    Q_DB --> S_FP & S_IMPORT
    
    S_FP --> ORM
    S_IMPORT --> ORM
    S_EXPORT --> ORM

    ORM <--> DB
```

---

## 2. Diagram Sekuensial (*Sequence Diagrams*)

### 2.1 Sekuensial 1: Transaksi Kasir POS dengan Pembayaran QRIS & Kamera Bukti

Diagram ini menggambarkan alur detail komunikasi dari saat kasir mengaktifkan kamera laptop/HP, memotret bukti berhasil transaksi QRIS pelanggan, hingga penyimpanan atomik ke database dan penyimpanan berkas bukti ke disk.

```mermaid
sequenceDiagram
    autonumber
    actor Kasir as Kasir Cabang
    participant View as View (transactions.create)
    participant Media as Browser MediaDevices API
    participant Ctrl as TransactionController
    participant Disk as Storage Disk (public)
    participant DB as Database (Transactions & Sequences)

    Kasir->>View: Buka Form POS & Pilih Menu Pesanan
    View-->>Kasir: Tampilkan Subtotal & Total Bayar
    Kasir->>View: Pilih Metode Pembayaran "QRIS"
    View->>View: Tampilkan Panel QRIS & Tombol Kamera

    Kasir->>View: Klik "Buka Kamera"
    View->>Media: navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
    Media-->>View: Stream Video Kamera Aktif (Tampil di elemen <video>)
    
    Kasir->>View: Arahkan ke Layar HP Pelanggan & Klik "Ambil Foto"
    View->>View: Render Video Frame ke HTML5 <canvas>
    View->>View: Ekspor ke Base64 Data URL (image/jpeg)
    View->>View: Tampilkan Thumbnail Pratinjau "Foto Siap"
    View->>Media: Matikan Track Stream Kamera (Stop Camera)

    Kasir->>View: Klik "Proses & Simpan Transaksi"
    View->>Kasir: Muncul Konfirmasi SweetAlert2 ("Simpan Transaksi?")
    Kasir->>View: Klik "Ya, Simpan Transaksi"

    View->>Ctrl: POST /transactions (cart JSON, branch_id, payment_method='qris', payment_proof_camera=base64)
    activate Ctrl

    Ctrl->>Ctrl: Validasi Input & Keranjang Belanja
    Ctrl->>Ctrl: handlePaymentProof() (Decode Base64 Data)
    Ctrl->>Disk: Storage::disk('public')->put('payment_proofs/qris_XXX.jpg', binaryData)
    Disk-->>Ctrl: Path Berkas Relatif Tersimpan

    Ctrl->>DB: DB::transaction(callback)
    activate DB
    DB->>DB: Lock Row di transaction_sequences (lockForUpdate)
    DB->>DB: Generate Nomor Urut Baru (TRXYYYYMMDDXXXXX)
    DB->>DB: INSERT into transactions (code, branch_id, total, payment_proof path)
    DB->>DB: INSERT batch into transaction_items (menu_id, qty, price, discount, subtotal)
    DB-->>Ctrl: Commit Transaksi Sukses
    deactivate DB

    Ctrl-->>View: Redirect to /transactions/{id} + Flash Success
    deactivate Ctrl

    View-->>Kasir: Tampilkan Detail Nota Transaksi & Kartu Foto Bukti QRIS
```

---

### 2.2 Sekuensial 2: Pencatatan Kas Operasional Harian & Saldo Kas Warung

Diagram ini menggambarkan alur pencatatan belanja harian warung (beras, gas, minyak, sabun) beserta validasi batas saldo kas cabang.

```mermaid
sequenceDiagram
    autonumber
    actor Kasir as Kasir Cabang
    participant View as View (operational-cash.create)
    participant Ctrl as OperationalCashController
    participant Model as Model OperationalCash
    participant DB as Database (operational_cashes)

    Kasir->>View: Buka Menu "Kas & Belanja Harian" -> Klik "+ Catat Transaksi Kas"
    View-->>Kasir: Tampilkan Formulir Kas (Jenis, Kategori, Nominal, Keterangan)
    Kasir->>View: Input Data (type='pengeluaran', category='Bahan Baku', amount=150000, desc='Beli beras & minyak')
    Kasir->>View: Klik "Simpan Transaksi Kas"

    View->>Ctrl: POST /operational-cash (type, category, amount, description, proof_file)
    activate Ctrl

    Ctrl->>Ctrl: Validasi Input (amount > 0, tipe valid)
    
    alt Jika Tipe adalah Pengeluaran
        Ctrl->>Model: getBranchBalance(user.branch_id)
        activate Model
        Model->>DB: SUM(pemasukan) - SUM(pengeluaran) cabang ini
        DB-->>Model: Nilai Saldo Kas Saat Ini (contoh: Rp 500.000)
        deactivate Model
        
        alt Jika Nominal Pengeluaran > Saldo Kas
            Ctrl-->>View: Redirect Back with Error ("Saldo kas cabang tidak mencukupi!")
            View-->>Kasir: Tampilkan Toast SweetAlert2 Merah
        end
    end

    opt Jika Melampirkan Foto Struk Fisik
        Ctrl->>Ctrl: Simpan Berkas Gambar ke storage/app/public/receipt_proofs/
    end

    Ctrl->>DB: Generate Kode Kas Unik (KAS-YYYYMMDD-XXXX)
    Ctrl->>DB: INSERT into operational_cashes (code, branch_id, type, amount, created_by)
    DB-->>Ctrl: Record Kas Berhasil Disimpan

    Ctrl-->>View: Redirect to /operational-cash with Success Toast
    deactivate Ctrl

    View-->>Kasir: Tampilkan Rekap Kas Harian (Kartu Masuk, Keluar, & Sisa Saldo Baru)
```

---

### 2.3 Sekuensial 3: Pipeline Algoritma FP-Growth (Data Mining Keranjang Belanja)

Diagram ini menggambarkan proses komputasi data mining dari inisiasi oleh Owner hingga pembentukan aturan asosiasi menu bundling.

```mermaid
sequenceDiagram
    autonumber
    actor Owner as Pemilik Bisnis / Admin
    participant View as View (analysis.fp-growth)
    participant Ctrl as FpGrowthController
    participant Queue as Laravel Queue (Database)
    participant Job as RunFpGrowthJob
    participant Svc as FpGrowthService
    participant DB as Database

    Owner->>View: Buka Analisis FP-Growth -> Masukkan Periode, Min Support (5%), Min Confidence (50%)
    Owner->>View: Klik "Mulai Analisis"
    
    View->>Ctrl: POST /fp-growth/run (name, date_from, date_to, min_support, min_confidence)
    activate Ctrl
    
    Ctrl->>DB: INSERT into fp_analyses (name, status='pending', created_by)
    DB-->>Ctrl: Objek Analisis (id = 10)
    
    Ctrl->>Queue: RunFpGrowthJob::dispatch(analysis_id=10)
    Queue-->>Ctrl: Job Didaftarkan ke Tabel jobs
    
    Ctrl-->>View: Redirect with Success ("Analisis berjalan di antrean latar belakang")
    deactivate Ctrl
    View-->>Owner: Tampilkan Status Sesi "Running..."

    Note over Queue,Job: Eksekusi Latar Belakang (Worker Thread)
    Queue->>Job: handle()
    activate Job
    Job->>DB: UPDATE fp_analyses SET status='running', started_at=NOW()
    
    Job->>DB: Kueri Transaksi & Detail Menu pada Periode Terpilih
    DB-->>Job: 3.230 Baris Transaksi Penjualan
    
    Job->>Svc: execute(transactions, minSupport=0.05, minConfidence=0.50)
    activate Svc
    
    Svc->>Svc: 1. Hitung Frekuensi 1-Itemset (Dukungan Individual Menu)
    Svc->>Svc: 2. Eliminasi Menu dengan Frekuensi < Min Support
    Svc->>Svc: 3. Urutkan Menu dalam Transaksi Berdasarkan Frekuensi Descending
    Svc->>Svc: 4. Bangun FP-Tree (Root Node, Child Nodes, & Header Table Linked-List)
    Svc->>Svc: 5. Rekursi Penambangan Conditional FP-Tree untuk Frequent k-Itemsets
    Svc->>Svc: 6. Bangun Aturan Asosiasi (X -> Y) & Hitung Confidence serta Lift Ratio
    Svc-->>Job: Hasil: 45 Frequent Itemsets & 18 Association Rules
    deactivate Svc

    Job->>DB: INSERT batch into fp_frequent_itemsets (analysis_id, items, support)
    Job->>DB: INSERT batch into fp_association_rules (analysis_id, antecedent, consequent, confidence, lift)
    Job->>DB: UPDATE fp_analyses SET status='done', finished_at=NOW(), total_transactions=3230
    deactivate Job

    Owner->>View: Refresh / Buka Detail Analisis (id=10)
    View->>DB: Ambil Hasil Analisis, Frequent Itemsets, & Rules Terurut Lift Terbesar
    DB-->>View: Data Lengkap
    View-->>Owner: Visualisasi Tabel Rekomendasi Menu Bundling & Pola Pembelian
```

---

## 3. Pemetaan Direktori & Komponen Kode (*Component Mapping*)

Untuk memudahkan navigasi arsitektur dan penelusuran kode, berikut adalah pemetaan komponen utama dalam repositori:

| Modul / Domain | Model Eloquent | Kontroler Utama | Views (Blade Templates) | Migrasi Database |
|---|---|---|---|---|
| **Multi-Cabang** | `app/Models/Branch.php` | Diintegrasikan ke Transaksi & User | Komponen Header & Dropdown | `2026_04_03_000001_create_branches...` |
| **Pengguna & Akses**| `app/Models/User.php` | `app/Http/Controllers/UserController.php` | `resources/views/users/` | `0001_01_01_000000_create_users_table.php` |
| **Katalog Menu** | `app/Models/Menu.php`, `Category.php` | `MenuController.php`, `CategoryController.php` | `resources/views/menus/`, `categories/` | `2026_04_01_000001_create_menus_and_categories...` |
| **POS & QRIS Kamera**| `app/Models/Transaction.php`, `TransactionItem.php`| `app/Http/Controllers/TransactionController.php` | `resources/views/transactions/` | `2026_04_01_000002_create_transactions...`, `...add_payment_proof...` |
| **Kas & Belanja Warung**| `app/Models/OperationalCash.php` | `app/Http/Controllers/OperationalCashController.php`| `resources/views/operational-cash/` | `2026_04_04_000001_create_operational_cashes...` |
| **Analisis FP-Growth**| `FpAnalysis.php`, `FpFrequentItemset.php`, `FpAssociationRule.php` | `app/Http/Controllers/FpGrowthController.php` | `resources/views/analysis/` | `2026_04_01_000003_create_fp_tables...` |
| **Dasbor Analitik** | Multi-Model Aggregation | `app/Http/Controllers/DashboardController.php` | `resources/views/dashboard/` | Menggunakan agregasi database kueri |
| **Laporan & Ekspor** | Multi-Model Aggregation | `app/Http/Controllers/ReportController.php` | `resources/views/reports/` | Format cetak Blade PDF & Excel |
| **Pengujian Fitur** | - | - | - | `tests/Feature/BranchAndPaymentTest.php`, `OperationalCashTest.php`, dll |
