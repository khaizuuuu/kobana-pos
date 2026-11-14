# 📊 Activity Diagram (Diagram Aktivitas)
## Pemodelan Alur Kerja & Logika Proses Bisnis Sistem POS
### Kobana Fried Chicken

---

## 1. Pendahuluan

*Activity Diagram* (Diagram Aktivitas) menggambarkan aspek dinamis dari sistem dengan memodelkan urutan langkah, percabangan keputusan (*decision points*), pemrosesan paralel (*fork & join*), serta pembagian tanggung jawab antar aktor dan sistem menggunakan jalur aktivitas (*swimlanes*).

Dokumen ini memuat diagram aktivitas untuk 7 proses inti pada sistem **Kobana POS & Analisis FP-Growth**:
1. **AD-01**: Autentikasi Pengguna & Penentuan Akses Cabang
2. **AD-02**: Transaksi Penjualan Kasir POS — Pembayaran Tunai (Cash)
3. **AD-03**: Transaksi Penjualan Kasir POS — Pembayaran QRIS & Kamera Bukti
4. **AD-04**: Pengelolaan Kas Kecil & Belanja Harian Warung
5. **AD-05**: Pipeline Komputasi Data Mining FP-Growth
6. **AD-06**: Monitoring Dasbor Analitik & Filter Cabang
7. **AD-07**: Impor Massal Dataset Penjualan Excel

---

## 2. AD-01: Autentikasi Pengguna & Penentuan Akses Cabang

Diagram ini memodelkan proses verifikasi identitas, pemeriksaan status aktif pengguna, serta penguncian konteks cabang toko.

```mermaid
flowchart TD
    START([Mulai]) --> INPUT_LOGIN[/Pengguna memasukkan Email dan Password/]
    INPUT_LOGIN --> SUBMIT_LOGIN[Kirim Permintaan POST /login]
    SUBMIT_LOGIN --> VAL_CREDENTIAL{Kredensial Cocok?}

    VAL_CREDENTIAL -- Tidak --> ERR_CRED[Tampilkan Error: 'Email atau password salah']
    ERR_CRED --> INPUT_LOGIN

    VAL_CREDENTIAL -- Ya --> CHK_ACTIVE{Akun Aktif<br/>is_active == 1?}

    CHK_ACTIVE -- Tidak --> ERR_INACTIVE[Tampilkan Error: 'Akun Anda dinonaktifkan']
    ERR_INACTIVE --> INPUT_LOGIN

    CHK_ACTIVE -- Ya --> REGEN_SESSION[Regenerasi ID Sesi & Pasang Proteksi CSRF]
    REGEN_SESSION --> CHK_ROLE{Pemeriksaan Peran<br/>Role Pengguna}

    CHK_ROLE -- Kasir --> CHK_BRANCH{Kasir Memiliki<br/>branch_id?}
    CHK_BRANCH -- Ya --> LOCK_BRANCH[Kunci Konteks Sesi ke Cabang Terdaftar]
    CHK_BRANCH -- Tidak --> SET_DEFAULT[Gunakan Cabang Utama / Kobana Pusat]
    LOCK_BRANCH --> REDIR_POS[Arahkan ke Halaman Kasir /transactions/create]
    SET_DEFAULT --> REDIR_POS

    CHK_ROLE -- Admin --> ALLOW_ALL_ADMIN[Buka Akses Penuh ke Seluruh Cabang]
    ALLOW_ALL_ADMIN --> REDIR_DASH[Arahkan ke Halaman Utama /dashboard]

    CHK_ROLE -- Owner --> ALLOW_ALL_OWNER[Buka Akses Monitoring Seluruh Cabang]
    ALLOW_ALL_OWNER --> REDIR_DASH

    REDIR_POS --> SUCCESS_TOAST[Tampilkan Toast Selamat Datang SweetAlert2]
    REDIR_DASH --> SUCCESS_TOAST
    SUCCESS_TOAST --> END([Selesai])
```

---

## 3. AD-02: Transaksi Penjualan Kasir POS — Pembayaran Tunai (Cash)

Diagram ini memodelkan alur pelayanan pelanggan di meja kasir dengan metode pembayaran uang tunai, kalkulasi kembalian otomatis, dan pencetakan nota.

```mermaid
flowchart TD
    START([Mulai Transaksi]) --> OPEN_POS[Kasir membuka layar POS /transactions/create]
    OPEN_POS --> SHOW_MENU[Sistem memuat daftar menu aktif & info cabang]
    SHOW_MENU --> SEARCH_MENU[/Kasir mencari menu via input pencarian/]
    SEARCH_MENU --> SELECT_ITEM[Kasir menentukan Qty & Diskon per item]
    SELECT_ITEM --> CLICK_ADD[Kasir klik tombol '+ Tambah']

    CLICK_ADD --> UPDATE_CART[Sistem menambahkan item ke tabel Keranjang]
    UPDATE_CART --> CALC_TOTAL[Hitung Total Item & Total Bayar Bersih]
    CALC_TOTAL --> MORE_ITEMS{Tambah menu<br/>lainnya?}

    MORE_ITEMS -- Ya --> SEARCH_MENU
    MORE_ITEMS -- Tidak --> CHOOSE_CASH[Pilih Metode Pembayaran: 'Cash']

    CHOOSE_CASH --> INPUT_CASH[/Kasir menginput Uang Tunai Diterima<br/>atau klik tombol cepat 20rb/50rb/100rb/]
    INPUT_CASH --> CALC_CHANGE[Sistem menghitung Kembalian secara real-time]
    CALC_CHANGE --> CHK_CASH{Uang Diterima >=<br/>Total Bayar?}

    CHK_CASH -- Tidak --> WARN_LESS[Indikator Merah: Uang Kurang & Tombol Simpan Disabled]
    WARN_LESS --> INPUT_CASH

    CHK_CASH -- Ya --> SHOW_CHANGE[Tampilkan Nominal Kembalian Hijau & Aktifkan Tombol Simpan]
    SHOW_CHANGE --> CLICK_SUBMIT[Kasir klik 'Proses & Simpan Transaksi']

    CLICK_SUBMIT --> SWAL_CONFIRM{Konfirmasi Modal<br/>SweetAlert2}
    SWAL_CONFIRM -- Batal --> INPUT_CASH
    SWAL_CONFIRM -- Ya, Simpan --> START_DB_TRX[Sistem membuka DB::transaction]

    START_DB_TRX --> LOCK_SEQ[Kunci baris tanggal di transaction_sequences]
    LOCK_SEQ --> GEN_CODE[Hasilkan Kode Unik TRXYYYYMMDDXXXXX]
    GEN_CODE --> SAVE_HEADER[Simpan ke tabel transactions]
    SAVE_HEADER --> SAVE_ITEMS[Simpan setiap baris ke tabel transaction_items]
    SAVE_ITEMS --> COMMIT_TRX[Commit Transaksi Database]

    COMMIT_TRX --> REDIR_SHOW[Arahkan ke Rincian Nota /transactions/{id}]
    REDIR_SHOW --> PRINT_PROMPT{Kasir ingin<br/>cetak struk?}

    PRINT_PROMPT -- Ya --> TRIGGER_PRINT[Jalankan window.print untuk Printer Thermal]
    PRINT_PROMPT -- Tidak --> END([Selesai])
    TRIGGER_PRINT --> END
```

---

## 4. AD-03: Transaksi Kasir POS — Pembayaran QRIS & Kamera Bukti

Diagram ini memodelkan penanganan pembayaran digital QRIS yang dilengkapi fitur penangkap kamera peramban (*webcam / mobile camera*) untuk merekam bukti transaksi berhasil.

```mermaid
flowchart TD
    START([Mulai Transaksi QRIS]) --> OPEN_POS[Kasir mengisi Keranjang Belanja]
    OPEN_POS --> CHOOSE_QRIS[Kasir memilih opsi pembayaran 'QRIS']
    CHOOSE_QRIS --> HIDE_CASH[Sistem menyembunyikan panel tunai & menampilkan panel QRIS]
    HIDE_CASH --> SHOW_QR_STATIC[Kasir menunjukkan kode QRIS Kobana ke Pelanggan]

    SHOW_QR_STATIC --> CUST_SCAN[/Pelanggan memindai QRIS & melakukan pembayaran di HP/]
    CUST_SCAN --> INPUT_REF[/Kasir memasukkan No. Referensi / RRN struk QRIS opsional/]

    INPUT_REF --> CHOOSE_CAPTURE_METHOD{Pilih Metode<br/>Bukti Bayar}

    %% Cabang Kamera Langsung
    CHOOSE_CAPTURE_METHOD -- Kamera Langsung --> CLICK_OPEN_CAM[Kasir klik tombol 'Buka Kamera']
    CLICK_OPEN_CAM --> REQ_STREAM[Sistem panggil navigator.mediaDevices.getUserMedia]
    REQ_STREAM --> PERM_CHECK{Izin Kamera<br/>Diberikan?}

    PERM_CHECK -- Ditolak / Tidak Ada --> SHOW_CAM_ERR[Tampilkan Peringatan: Gunakan Tombol Unggah File]
    SHOW_CAM_ERR --> CLICK_PICK_FILE

    PERM_CHECK -- Diterima --> PLAY_VIDEO[Aktifkan elemen video streaming langsung]
    PLAY_VIDEO --> AIM_CAMERA[Kasir mengarahkan kamera ke layar HP pelanggan]
    AIM_CAMERA --> CLICK_SNAP[Kasir klik 'Ambil Foto']
    CLICK_SNAP --> DRAW_CANVAS[Sistem merender frame video ke elemen canvas HTML5]
    DRAW_CANVAS --> EXPORT_BASE64[Konversi canvas ke dataURL JPEG 85%]
    EXPORT_BASE64 --> STOP_CAM[Hentikan semua track stream kamera]
    STOP_CAM --> SET_PREVIEW[Tampilkan gambar pada kartu pratinjau 'Foto Siap']

    %% Cabang Unggah Screenshot
    CHOOSE_CAPTURE_METHOD -- Unggah Screenshot --> CLICK_PICK_FILE[Kasir klik tombol 'Unggah File']
    CLICK_PICK_FILE --> SELECT_IMAGE_FILE[/Kasir memilih file gambar screenshot/]
    SELECT_IMAGE_FILE --> READ_FILE_READER[FileReader membaca file ke DataURL]
    READ_FILE_READER --> SET_PREVIEW

    SET_PREVIEW --> CHECK_PHOTO_OK{Kasir puas<br/>dengan foto?}
    CHECK_PHOTO_OK -- Ambil Ulang --> CLICK_DELETE_PROOF[Kasir klik 'Hapus']
    CLICK_DELETE_PROOF --> CHOOSE_CAPTURE_METHOD

    CHECK_PHOTO_OK -- Ya, Lanjut --> CLICK_SAVE_TRX[Kasir klik 'Proses & Simpan Transaksi']
    CLICK_SAVE_TRX --> POPUP_CONFIRM[Dialog Konfirmasi SweetAlert2]
    POPUP_CONFIRM --> SEND_POST[Kirim Form dengan Base64 / File Multipart]

    SEND_POST --> HANDLE_PROOF_BACKEND[Backend TransactionController::handlePaymentProof]
    HANDLE_PROOF_BACKEND --> WRITE_STORAGE[Simpan file ke storage/app/public/payment_proofs/]
    WRITE_STORAGE --> DB_ATOMIC[Jalankan DB::transaction dengan Row-Locking]
    DB_ATOMIC --> SAVE_TRX_QRIS[Simpan Transaction dengan kolom payment_proof terisi]
    SAVE_TRX_QRIS --> REDIR_DETAIL[Arahkan ke Nota Detail]
    REDIR_DETAIL --> SHOW_LIGHTBOX[Tampilkan kartu Bukti QRIS + Tombol Perbesar Foto]
    SHOW_LIGHTBOX --> END([Selesai])
```

---

## 5. AD-04: Pengelolaan Kas Kecil & Belanja Harian Warung

Diagram ini memodelkan proses pencatatan belanja kebutuhan dapur warung (beras, gas LPG, minyak goreng, kantong kresek, teh/gula, sabun) serta setoran tabungan hasil penjualan harian.

```mermaid
flowchart TD
    START([Mulai Kas Harian]) --> OPEN_CASH_PAGE[Kasir membuka menu 'Kas & Belanja Harian']
    OPEN_CASH_PAGE --> SHOW_RECAP[Sistem memuat kartu: Total Masuk, Total Keluar, & Saldo Kas Cabang]
    SHOW_RECAP --> CLICK_ADD_CASH[Kasir klik '+ Catat Transaksi Kas']

    CLICK_ADD_CASH --> CHOOSE_TYPE{Pilih Jenis<br/>Transaksi Kas}

    %% Cabang Pengeluaran (Belanja Warung)
    CHOOSE_TYPE -- Pengeluaran (Belanja) --> SELECT_EXPENSE_CAT[/Kasir memilih Kategori: Bahan Baku, Gas, Plastik, Sabun/]
    SELECT_EXPENSE_CAT --> INPUT_EXPENSE_VAL[/Input Nominal Belanja Rupiah & Keterangan Rinci/]
    INPUT_EXPENSE_VAL --> OPT_EXPENSE_RECEIPT[/Opsional: Unggah Foto Struk Belanja Pasar/Toko/]
    OPT_EXPENSE_RECEIPT --> CLICK_SUBMIT_EXPENSE[Kasir klik 'Simpan Transaksi Kas']

    CLICK_SUBMIT_EXPENSE --> BACKEND_VAL_BALANCE[Sistem menghitung Saldo Kas Cabang saat ini]
    BACKEND_VAL_BALANCE --> CHK_ENOUGH_BALANCE{Saldo Kas >=<br/>Nominal Belanja?}

    CHK_ENOUGH_BALANCE -- Tidak Cukup --> ERR_BALANCE_TOAST[Tampilkan Pesan Error: 'Saldo kas cabang tidak mencukupi!']
    ERR_BALANCE_TOAST --> INPUT_EXPENSE_VAL

    CHK_ENOUGH_BALANCE -- Cukup --> GEN_CASH_CODE[Hasilkan Kode Unik KAS-YYYYMMDD-XXXX]

    %% Cabang Pemasukan (Tabungan Penjualan)
    CHOOSE_TYPE -- Pemasukan (Setor Tabungan) --> INPUT_INCOME_VAL[/Input Nominal Kas Masuk & Keterangan/]
    INPUT_INCOME_VAL --> CLICK_SUBMIT_INCOME[Kasir klik 'Simpan Transaksi Kas']
    CLICK_SUBMIT_INCOME --> GEN_CASH_CODE

    GEN_CASH_CODE --> SAVE_CASH_DB[Simpan transaksi ke tabel operational_cashes]
    SAVE_CASH_DB --> UPDATE_BALANCE[Sistem mengupdate akumulasi saldo cabang]
    UPDATE_BALANCE --> SHOW_SUCCESS[Tampilkan Toast Berhasil SweetAlert2]
    SHOW_SUCCESS --> OPT_PRINT_RECEIPT{Cetak Bukti<br/>Kas Kecil?}

    OPT_PRINT_RECEIPT -- Ya --> PRINT_CASH[Cetak Bukti Pengeluaran Kas untuk LPJ]
    OPT_PRINT_RECEIPT -- Tidak --> END([Selesai])
    PRINT_CASH --> END
```

---

## 6. AD-05: Pipeline Komputasi Data Mining FP-Growth

Diagram ini memodelkan proses penambangan aturan asosiasi keranjang belanja secara asinkron menggunakan pohon *FP-Tree* pada ribuan transaksi.

```mermaid
flowchart TD
    START([Mulai Analisis]) --> OPEN_FP[Owner / Admin membuka menu 'Analisis FP-Growth']
    OPEN_FP --> INPUT_PARAMS[/Input Judul Sesi, Tanggal Awal & Akhir, Min Support %, Min Confidence %/]
    INPUT_PARAMS --> CLICK_RUN[Klik 'Mulai Analisis']

    CLICK_RUN --> CREATE_PENDING_RECORD[Sistem membuat record di tabel fp_analyses status='pending']
    CREATE_PENDING_RECORD --> DISPATCH_JOB[Kirim RunFpGrowthJob ke Antrean Queue Database]
    DISPATCH_JOB --> FLASH_MSG[Tampilkan Notifikasi: 'Analisis berjalan di antrean latar belakang']

    subgraph Background_Queue_Worker [Pekerja Latar Belakang (Queue Worker)]
        START_WORKER([Worker Mengambil Job]) --> SET_RUNNING[Update status='running', started_at=NOW]
        SET_RUNNING --> QUERY_TRX[Ambil data transaksi & detail item pada rentang tanggal]
        QUERY_TRX --> CHK_TRX_EXIST{Transaksi<br/>Ditemukan?}

        CHK_TRX_EXIST -- Tidak Ada Data --> SET_FAILED[Update status='failed' dengan pesan error]
        SET_FAILED --> END_WORKER([Job Gagal])

        CHK_TRX_EXIST -- Ada Data --> CALC_1_ITEMSET[1. Hitung Frekuensi Kemunculan Tiap Menu 1-Itemset]
        CALC_1_ITEMSET --> PRUNE_MIN_SUPP[2. Eliminasi Menu dengan Support < Min Support]
        PRUNE_MIN_SUPP --> SORT_TRANSACTIONS[3. Urutkan Menu dalam Tiap Keranjang secara Descending]
        SORT_TRANSACTIONS --> BUILD_FPTREE[4. Bangun FP-Tree & Hubungkan Header Table Linked-List]
        BUILD_FPTREE --> MINE_TREE[5. Rekursi Penambangan Conditional FP-Tree untuk Pola Majemuk]
        MINE_TREE --> EXTRACT_FREQUENT[6. Ekstraksi Daftar Frequent Itemsets]
        EXTRACT_FREQUENT --> SAVE_ITEMSETS[Simpan ke tabel fp_frequent_itemsets]

        SAVE_ITEMSETS --> GEN_RULES[7. Bentuk Aturan Asosiasi X -> Y yang Memenuhi Min Confidence]
        GEN_RULES --> CALC_LIFT[8. Hitung Nilai Korelasi Lift Ratio]
        CALC_LIFT --> FILTER_LIFT[9. Filter Aturan Signifikan Lift > 1.0]
        FILTER_LIFT --> SAVE_RULES[Simpan ke tabel fp_association_rules]

        SAVE_RULES --> SET_DONE[Update status='done', finished_at=NOW, total_transactions]
        SET_DONE --> END_WORKER_OK([Job Sukses Selesai])
    end

    FLASH_MSG --> POLL_OR_REFRESH[/Owner membuka rincian hasil analisis/]
    END_WORKER_OK -.-> POLL_OR_REFRESH
    POLL_OR_REFRESH --> RENDER_RESULTS[Sistem menyajikan tabel Frequent Itemsets & Rekomendasi Menu Bundling]
    RENDER_RESULTS --> END([Selesai])
```

---

## 7. AD-06: Monitoring Dasbor Analitik & Filter Cabang

Diagram ini memodelkan proses eksplorasi performa operasional dan finansial penjualan lintas cabang oleh Owner dan Admin.

```mermaid
flowchart TD
    START([Mulai Monitoring]) --> OPEN_DASH[Owner / Admin membuka Dashboard /dashboard]
    OPEN_DASH --> LOAD_DEFAULT[Sistem memuat data bawaan 30 hari terakhir untuk Semua Cabang]
    LOAD_DEFAULT --> RENDER_CARDS[Tampilkan Kartu KPI: Omzet, Trx, Item Terjual, Average Order Value]
    RENDER_CARDS --> RENDER_PAYMENT[Tampilkan Rasio Omzet Cash vs QRIS]
    RENDER_PAYMENT --> RENDER_CHARTS[Gambar Grafik: Tren Harian, Peak Hours, Hari Populer, Top 10 Menu, Donut Kategori]

    RENDER_CHARTS --> USER_FILTER{Owner ingin<br/>saring data?}

    USER_FILTER -- Ya --> SELECT_DATE_RANGE[/Pilih Rentang Tanggal: Dari Tanggal & Sampai Tanggal/]
    SELECT_DATE_RANGE --> SELECT_BRANCH[/Pilih Cabang Tertentu: Kobana Pusat / Sukun / Dinoyo/]
    SELECT_BRANCH --> CLICK_APPLY[Klik tombol 'Terapkan']

    CLICK_APPLY --> QUERY_AGGREGATE[Backend mengeksekusi kueri agregasi SQL dengan filter branch_id & tanggal]
    QUERY_AGGREGATE --> RENDER_CARDS

    USER_FILTER -- Tidak --> CHOOSE_EXPORT{Ekspor Laporan<br/>Penjualan?}

    CHOOSE_EXPORT -- Ya --> OPEN_REPORT[Buka Menu Laporan /reports]
    OPEN_REPORT --> CHOOSE_FORMAT{Pilih Format<br/>Laporan}
    CHOOSE_FORMAT -- Excel --> GEN_EXCEL[Unduh Dokumen Spreadsheet .xlsx Lengkap]
    CHOOSE_FORMAT -- PDF --> GEN_PDF[Unduh Dokumen Cetak Resmi .pdf]
    GEN_EXCEL --> END([Selesai])
    GEN_PDF --> END

    CHOOSE_EXPORT -- Tidak --> END
```

---

## 8. AD-07: Impor Massal Dataset Penjualan Excel

Diagram ini memodelkan proses pemuatan ribuan baris data penjualan historis melalui berkas spreadsheet.

```mermaid
flowchart TD
    START([Mulai Impor]) --> OPEN_IMPORT[Admin membuka menu 'Impor Data Penjualan']
    OPEN_IMPORT --> SELECT_FILE[/Admin memilih file kobana_dataset.xlsx/]
    SELECT_FILE --> CLICK_UPLOAD[Klik tombol 'Mulai Impor Data']

    CLICK_UPLOAD --> VALIDATE_MIME{Validasi File<br/>.xlsx / .xls?}
    VALIDATE_MIME -- Tidak Valid --> ERR_MIME[Tampilkan Pesan Error Format File]
    ERR_MIME --> SELECT_FILE

    VALIDATE_MIME -- Valid --> SAVE_TEMP[Simpan file sementara ke storage lokal]
    SAVE_TEMP --> CREATE_LOG[Buat catatan di tabel import_logs status='pending']
    CREATE_LOG --> DISPATCH_IMPORT_JOB[Daftarkan ProcessDatasetImportJob ke Antrean Queue]

    subgraph Worker_Import_Thread [Worker Antrean Impor]
        PICK_JOB([Worker Membuka Spreadsheet]) --> SET_RUNNING_IMPORT[Update status='running']
        SET_RUNNING_IMPORT --> READ_SHEETS[Baca Sheets: Master_Menu, Header_Transaksi, Detail_Transaksi]
        
        READ_SHEETS --> LOOP_MENU[1. Iterasi Master_Menu: Validasi Kategori & Menu Baru]
        LOOP_MENU --> LOOP_HEADER[2. Iterasi Header_Transaksi: Parse Tanggal, Jam, & Total]
        LOOP_HEADER --> LOOP_ITEMS[3. Iterasi Detail_Transaksi: Relasikan menu_id, qty, & hitung diskon]
        
        LOOP_ITEMS --> CHK_ROW_ERRORS{Ditemukan baris<br/>tidak konsisten?}
        CHK_ROW_ERRORS -- Ya --> RECORD_WARNING[Catat detail baris ke JSON warnings tanpa membatalkan proses]
        CHK_ROW_ERRORS -- Tidak --> SAVE_ALL_IMPORT[Tulis seluruh baris ke database]
        RECORD_WARNING --> SAVE_ALL_IMPORT

        SAVE_ALL_IMPORT --> FINISH_LOG[Update import_logs status='done', rows_menu, rows_transactions, rows_items]
        FINISH_LOG --> END_WORKER_IMP([Impor Selesai])
    end

    DISPATCH_IMPORT_JOB --> VIEW_LOGS[Admin melihat daftar riwayat log impor]
    END_WORKER_IMP -.-> VIEW_LOGS
    VIEW_LOGS --> SHOW_RESULT[Tampilkan ringkasan total baris berhasil & rincian peringatan]
    SHOW_RESULT --> END([Selesai])
```
