# 📚 Dokumentasi Perancangan Sistem Kobana POS & Analisis FP-Growth

Selamat datang di direktori dokumentasi perancangan sistem **Kobana Fried Chicken (Kobana POS)**. Direktori ini memuat seluruh dokumen analisis dan perancangan sistem (*System Design & Architecture Documentation*) yang mencakup kebutuhan produk, pemodelan data, diagram aliran data, diagram *use case*, arsitektur perangkat lunak, serta diagram sekuensial.

---

## 🗂️ Daftar Dokumen Perancangan

| Dokumen | Nama Berkas | Deskripsi Singkat |
|---|---|---|
| **Product Requirements Document (PRD)** | [`docs/PRD.md`](./PRD.md) | Spesifikasi kebutuhan fungsional & non-fungsional, personas, batasan sistem, aturan bisnis, dan matriks hak akses. |
| **Entity Relationship Diagram (ERD)** | [`docs/ERD.md`](./ERD.md) | Pemodelan data konseptual, logikal, dan fisik lengkap dengan diagram Mermaid ERD, kamus data (*data dictionary*), dan integritas referensial. |
| **Data Flow Diagram (DFD)** | [`docs/DFD.md`](./DFD.md) | Diagram aliran data Level 0 (*Context Diagram*), Level 1 (Sistem Utama), dan Level 2 (Dekomposisi Proses Transaksi, Kas, dan FP-Growth). |
| **Use Case Diagram & Spesifikasi** | [`docs/USE_CASE.md`](./USE_CASE.md) | Diagram interaksi aktor (Kasir, Admin, Owner) serta skenario rinci (*main flow*, *alternate flow*, *precondition*, *postcondition*). |
| **Activity Diagram (Diagram Aktivitas)** | [`docs/ACTIVITY_DIAGRAM.md`](./ACTIVITY_DIAGRAM.md) | Pemodelan alur kerja dinamis 7 proses bisnis utama (login & cabang, transaksi tunai, transaksi QRIS & kamera bukti, kas harian warung, pipeline data mining FP-Growth, dashboard analitik, dan impor dataset). |
| **Arsitektur & Diagram Sekuensial** | [`docs/ARCHITECTURE_AND_SEQUENCE.md`](./ARCHITECTURE_AND_SEQUENCE.md) | Desain arsitektur MVC tiga lapis, pipeline algoritma FP-Growth, dan *sequence diagrams* alur transaksi QRIS kamera serta kas harian. |
| **Panduan Deploy VPS & Docker** | [`docs/DOCKER_DEPLOYMENT_GUIDE.md`](./DOCKER_DEPLOYMENT_GUIDE.md) | Panduan komprehensif deployment VPS menggunakan Docker Compose (Nginx Alpine, PHP 8.3 FPM Alpine, MySQL 8.0, Queue Worker, & Scheduler). |

---

## 🛠️ Ringkasan Profil & Arsitektur Sistem

- **Nama Aplikasi:** Kobana POS & Market Basket Analysis (FP-Growth)
- **Kategori:** *Point of Sale* (POS), *Retail Multi-Store Management*, dan *Data Mining Engine*
- **Framework & Bahasa:** Laravel 13 (PHP 8.3+), Eloquent ORM, Blade Templating Engine
- **Frontend Layer:** Bootstrap 5.3.3 (CDN), Bootstrap Icons 1.11.3, SweetAlert2, Chart.js 4 (Tanpa Node build step / Vite)
- **Database Engine:** MySQL 8.0+ / MariaDB 10.4+ / SQLite (Testing & Dev)
- **Penyimpanan Berkas:** Local Filesystem Public Storage (`storage/app/public/`) dengan tautan simbolik `public/storage`
- **Driver Antrean (Queue):** Database Driver (`jobs`, `failed_jobs`)
- **Zona Waktu & Mata Uang:** `Asia/Jakarta` (WIB), Rupiah Indonesia (`Rp`)

---

## 🏬 Struktur Cabang & Operasional Bisnis

Aplikasi dirancang untuk mendukung operasional multi-cabang terpadu:
1. **Kobana Pusat (`KBN01`)** — Jl. Soekarno Hatta No. 12, Malang (Pusat Operasional)
2. **Kobana Sukun (`KBN02`)** — Jl. S. Supriadi No. 45, Malang (Outlet Cabang)
3. **Kobana Dinoyo (`KBN03`)** — Jl. MT Haryono No. 88, Malang (Outlet Cabang)

Setiap kasir terisolasi pada cabangnya saat transaksi dan kas kecil, sedangkan Pemilik (*Owner*) dan Administrator dapat mengelola dan memantau seluruh cabang secara terpusat (*Centralized Executive Analytics*).
