# Graph Report - antigravity  (2026-11-14)

## Corpus Check
- 156 files · ~80,871 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 21 file(s) not represented in the graph (top: (none) 16, .css 2, .example 1)

## Summary
- 1108 nodes · 1569 edges · 107 communities (53 shown, 54 thin omitted)
- Extraction: 95% EXTRACTED · 5% INFERRED · 0% AMBIGUOUS · INFERRED: 72 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Branch Management & Multi-Store Data
- Database Migrations & Schema Definitions
- System Design & Architecture Documentation
- Architecture Principles & Best Practices
- Data Import Commands & Format Helpers
- POS User Guide & Workflow Documentation
- Blade Components & UI Views
- Agent Setup & Configuration Guidelines
- Installation & Deployment Procedures
- FP-Growth Algorithm & Market Basket Analysis
- Composer Dependencies & Autoload Config
- User Model & Role Authorization
- Authentication & Password Management
- Operational Cash & Petty Expenses
- POS Transaction & Order Processing
- Base Test Suites & Unit Testing
- Category Management & Taxonomy
- Menu Catalog & Item Management
- Payment Proof & Camera Tests
- Dashboard Metrics & Chart Analytics
- Categorycontroller.Php Module
- Importcontroller.Php Module
- 11 Entitas Luar External Entities Module
- Illuminate\Foundation\Testing\Testc Module
- Illuminate\Http\Uploadedfile Module
- A Validation  Http Input Module
- Skill Module
- Apply Cross-Site Request Forgery Pr Module
- Cache.Php Module
- 1 Pendahuluan Module
- 1 Definisi Aktor Sistem Module
- Controller.Php Module
- Config Module
- Advanced Query Best Practices Module
- Cache Event Discovery During Produc Module
- Back Off Transient Failures Module
- Basic Usage Module
- 1 Arsitektur Perangkat Lunak Tingka Module
- Illuminate\Support\Facades\Hash Module
- Caching Best Practices Module
- Add Indexes For Measured Query Patt Module
- Apply Global Scopes Sparingly Module
- Require Module
- Fpgrowthcontroller.Php Module
- Menucontroller Module
- App.Php Module
- Documentation Files Module
- Add Context To Exception Classes Module
- Bound Work Inside The Task Module
- Endpoint Tests Module
- Database Module
- Operationalcashtest.Php Module
- Choose Between Cursor And Lazy Module
- Http Client Module
- Assert The Delivery Mode Module
- Convention And Style Best Practices Module
- Arrange Act Assert Module
- Common Errors Module
- Assertions Module
- A Plaintext Env File Committed To T Module
- Naming Module
- Consistency First Module
- Rolemiddleware.Php Module
- Data Providers Module
- Daftar Dokumen Perancangan Module
- Phpunit\Framework\Testcase Module
- Laravel-Boost Module
- Security Module
- Layouts.Guest Module
- Index.Blade.Php Module
- Show.Blade.Php Module
- Change-Password.Blade.Php Module
- Create.Blade.Php Module
- Edit.Blade.Php Module
- Index.Blade.Php Module
- Index.Blade.Php Module
- Index.Blade.Php Module
- App.Blade.Php Module
- Guest.Blade.Php Module
- Create.Blade.Php Module
- Edit.Blade.Php Module
- Index.Blade.Php Module
- Create.Blade.Php Module
- Edit.Blade.Php Module
- Index.Blade.Php Module
- Index.Blade.Php Module
- Create.Blade.Php Module
- Edit.Blade.Php Module
- Index.Blade.Php Module
- Show.Blade.Php Module
- Create.Blade.Php Module
- Edit.Blade.Php Module
- Index.Blade.Php Module
- Robots Module

## God Nodes (most connected - your core abstractions)
1. `User` - 43 edges
2. `Transaction` - 34 edges
3. `Panduan Penggunaan` - 32 edges
4. `Readme` - 30 edges
5. `Prd` - 29 edges
6. `Menu` - 28 edges
7. `Claude` - 25 edges
8. `Kobana Prd Erd Prompt` - 23 edges
9. `Skill` - 22 edges
10. `Category` - 21 edges

## Surprising Connections (you probably didn't know these)
- `B Controllers  routing` --conceptually_related_to--> `Controller`  [INFERRED]
  .claude/skills/infer-conventions/references/checklist.md → app/Http/Controllers/Controller.php
- `Use ShouldDispatchAfterCommit Inside Transactions` --conceptually_related_to--> `Transaction`  [INFERRED]
  .claude/skills/laravel-best-practices/rules/events-notifications.md → app/Models/Transaction.php
- `Inject Required Dependencies` --conceptually_related_to--> `require`  [INFERRED]
  .claude/skills/laravel-best-practices/rules/architecture.md → composer.json
- `Configure Failover Cache Stores in Production` --conceptually_related_to--> `config`  [INFERRED]
  .claude/skills/laravel-best-practices/rules/caching.md → composer.json
- `Configuration Best Practices` --conceptually_related_to--> `config`  [INFERRED]
  .claude/skills/laravel-best-practices/rules/config.md → composer.json

## Import Cycles
- None detected.

## Communities (107 total, 54 thin omitted)

### Community 0 - "Branch Management & Multi-Store Data"
Cohesion: 0.06
Nodes (25): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+17 more)

### Community 1 - "Database Migrations & Schema Definitions"
Cohesion: 0.07
Nodes (8): Branch, FpAnalysis, FpAssociationRule, FpFrequentItemset, ImportLog, Menu, OperationalCash, 31 Tabel branches Master Cabang Toko

### Community 2 - "System Design & Architecture Documentation"
Cohesion: 0.04
Nodes (46): APIs  Eloquent Resources, Application Structure  Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Claude, Foundational Context (+38 more)

### Community 3 - "Architecture Principles & Best Practices"
Cohesion: 0.04
Nodes (48): Architecture Best Practices, Depend on Contracts at Boundaries, Architecture, Extract Focused Business Operations, Follow Framework Conventions, Inject Required Dependencies, Specify a Deterministic Sort Order, Use Atomic Locks for Race Conditions (+40 more)

### Community 4 - "Data Import Commands & Format Helpers"
Cohesion: 0.06
Nodes (6): format_rupiah(), format_tanggal(), nama_hari(), DashboardAnalyticsController, TransactionItem, DatabaseSeeder

### Community 5 - "POS User Guide & Workflow Documentation"
Cohesion: 0.10
Nodes (4): FpGrowthService, FpTreeNode, DummyDataSeeder, FpGrowthServiceTest

### Community 6 - "Blade Components & UI Views"
Cohesion: 0.06
Nodes (33): 10 Manajemen Pengguna User  Profil, 11 Tips  Troubleshooting, 1 Akses Sistem  Akun Pengguna, 2 Dashboard Analitik, 3 Kasir  Input Transaksi Baru POS, 4 Riwayat  Detail Transaksi, 5 Kas  Belanja Harian Warung Petty Cash  Tabungan, 6 Master Menu  Master Kategori (+25 more)

### Community 7 - "Agent Setup & Configuration Guidelines"
Cohesion: 0.08
Nodes (30): Build and Deploy, Blade and View Best Practices, Blade Views, Prefer Components for Explicit Interfaces, Return Blade Fragments for Partial Rendering, Share Compatible View Data with a View Composer, Share Parent Component Props with aware, Use attributes-merge in Component Templates (+22 more)

### Community 8 - "Installation & Deployment Procedures"
Cohesion: 0.07
Nodes (31): Agent Setup, Agents, Laravel Application, Prerequisites, Adding a cache to an existing environment, Adding a database to an existing environment, Checklists for Multi-Step Operations, Custom domain setup (+23 more)

### Community 9 - "FP-Growth Algorithm & Market Basket Analysis"
Cohesion: 0.07
Nodes (30): 1 Clone  ekstrak repositori, 2 Install dependency PHP, 3 Salin file environment, 4 Generate application key, 5 Jalankan migrasi  seeder, 6 Opsional Jalankan development server, Algoritma FP-Growth, Analisis FP-Growth analysisfp-growth admin  owner (+22 more)

### Community 10 - "Composer Dependencies & Autoload Config"
Cohesion: 0.12
Nodes (10): User, AppServiceProvider, {closure#10}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}() (+2 more)

### Community 11 - "User Model & Role Authorization"
Cohesion: 0.07
Nodes (27): 1 Informasi Dokumen  Riwayat Versi, 21 Latar Belakang, 22 Pernyataan Masalah Problem Statement, 2 Latar Belakang  Pernyataan Masalah, 31 Persona Pengguna, 32 Matriks Hak Akses Role-Based Access Control, 3 Profil Pengguna User Personas  Matriks Hak Akses, 61 Formula Transaksi Kasir (+19 more)

### Community 12 - "Authentication & Password Management"
Cohesion: 0.18
Nodes (4): ImportDatasetCommand, ProcessDatasetImportJob, RunFpGrowthJob, DatasetImportService

### Community 13 - "Operational Cash & Petty Expenses"
Cohesion: 0.09
Nodes (23): 10 Kriteria penerimaan acceptance criteria, 11 Fase pengerjaan kerjakan berurutan, 1 Peran  tujuan, 2 Pengguna  peran, 3 Tech stack, 4 Skema database ERD, 5 Impor dataset, 7 Kebutuhan non-fungsional (+15 more)

### Community 15 - "Base Test Suites & Unit Testing"
Cohesion: 0.17
Nodes (3): {closure#1}(), {closure#4}(), TransactionController

### Community 17 - "Menu Catalog & Item Management"
Cohesion: 0.12
Nodes (16): 1 Pemodelan Data Konseptual, 2 Diagram ERD Mermaid Diagram, 310 Tabel fp_frequent_itemsets Pola Itemset Frekuensi Tinggi, 311 Tabel fp_association_rules Aturan Asosiasi Rekomendasi Menu, 312 Tabel import_logs Audit Jejak Impor Dataset, 32 Tabel users Pengguna  Hak Akses, 33 Tabel categories Kategori Menu, 34 Tabel menus Katalog Produk (+8 more)

### Community 18 - "Payment Proof & Camera Tests"
Cohesion: 0.15
Nodes (6): Transaction, 35 Tabel transactions Header Penjualan POS, 36 Tabel transaction_items Rincian Item Penjualan POS, 37 Tabel transaction_sequences Pengendali Sekuensial Struk, POS Kasir transactionscreate, SidebarFeaturesTest

### Community 22 - "11 Entitas Luar External Entities Module"
Cohesion: 0.15
Nodes (13): 11 Entitas Luar External Entities, 12 Penyimpanan Data Data Stores, 1 Notasi  Entitas Sistem, 2 DFD Level 0  Diagram Konteks Context Diagram, 3 DFD Level 1  Dekomposisi Sistem Utama, 41 Dekomposisi Proses 30 Pemrosesan Transaksi POS  Pembayaran QRIS Kamera, 42 Dekomposisi Proses 40 Pencatatan Kas  Belanja Harian Warung, 43 Dekomposisi Proses 50 Pipeline Algoritma FP-Growth (+5 more)

### Community 23 - "Illuminate\Foundation\Testing\Testc Module"
Cohesion: 0.19
Nodes (3): DashboardAnalyticsTest, ExampleTest, TestCase

### Community 25 - "A Validation  Http Input Module"
Cohesion: 0.17
Nodes (12): A Validation  HTTP input, B Controllers  routing, C Authorization, D Eloquent  models, Detection Checklist, Checklist, E Architecture  organization, F Frontend  views (+4 more)

### Community 26 - "Skill Module"
Cohesion: 0.17
Nodes (12): Skill, Edge cases, Glob mapping, Ground Rules read before you start, Infer Conventions, Process, Step 0 Orient, Step 1 Predefined sweep (+4 more)

### Community 27 - "Apply Cross-Site Request Forgery Pr Module"
Cohesion: 0.17
Nodes (12): Apply Cross-Site Request Forgery Protection, Audit Dependencies, Authorize Protected Actions, Bind Query Parameters, Control Mass Assignment, Security, Encrypt Sensitive Attributes When Appropriate, Escape Output in Its Context (+4 more)

### Community 29 - "1 Pendahuluan Module"
Cohesion: 0.17
Nodes (12): 1 Pendahuluan, 2 AD-01 Autentikasi Pengguna  Penentuan Akses Cabang, 3 AD-02 Transaksi Penjualan Kasir POS  Pembayaran Tunai Cash, 4 AD-03 Transaksi Kasir POS  Pembayaran QRIS  Kamera Bukti, 5 AD-04 Pengelolaan Kas Kecil  Belanja Harian Warung, 6 AD-05 Pipeline Komputasi Data Mining FP-Growth, 7 AD-06 Monitoring Dasbor Analitik  Filter Cabang, 8 AD-07 Impor Massal Dataset Penjualan Excel (+4 more)

### Community 30 - "1 Definisi Aktor Sistem Module"
Cohesion: 0.17
Nodes (12): 1 Definisi Aktor Sistem, 2 Diagram Use Case Mermaid Diagram, 3 Matriks Keterlacakan Traceability Matrix, 4 Spesifikasi Kasus Penggunaan Rinci Use Case Specifications, Analisis Interaksi Aktor  Sistem POS Kobana, Use Case, Kobana Fried Chicken, UC-04 Transaksi Penjualan Kasir POS (+4 more)

### Community 31 - "Controller.Php Module"
Cohesion: 0.22
Nodes (9): Controller, DashboardController, Routing, Keep Controllers Focused on HTTP Concerns, Organize Controllers Around Resources, Routing and Controller Best Practices, Scope Nested Bindings, Use Implicit Route Model Binding (+1 more)

### Community 32 - "Config Module"
Cohesion: 0.20
Nodes (10): Config, Configuration and Resources, CSS-First Configuration, pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader (+2 more)

### Community 33 - "Advanced Query Best Practices Module"
Cohesion: 0.20
Nodes (10): Advanced Query Best Practices, Combine Related Counts with Conditional Aggregates, Compare whereHas with an IN Subquery, Consider a Correlated Subquery for Has-Many Ordering, Create Dynamic Relationships with a Subquery Foreign Key, Design Composite Indexes for the Query, Advanced Queries, Measure Two Simple Queries Against One Complex Query (+2 more)

### Community 34 - "Cache Event Discovery During Produc Module"
Cohesion: 0.20
Nodes (10): Cache Event Discovery During Production Deployment, Dispatch Queued Notifications After Commit, Events Notifications, Events and Notifications Best Practices, Implement HasLocalePreference on Notifiable Models, Queue Slow Notifications, Rely on Event Discovery, Route Notification Channels to Dedicated Queues (+2 more)

### Community 35 - "Back Off Transient Failures Module"
Cohesion: 0.20
Nodes (10): Back Off Transient Failures, Batch Jobs for Group Coordination, Configure Time-Based Retry Limits Deliberately, Queue Jobs, Handle Terminal Failure When Needed, Keep Reservation Time Longer Than Execution Time, Queue and Job Best Practices, Rate Limit External Calls (+2 more)

### Community 36 - "Basic Usage Module"
Cohesion: 0.20
Nodes (10): Basic Usage, Common Pitfalls, Dark Mode, Skill, Documentation, Import Syntax, Replaced Utilities, Spacing (+2 more)

### Community 37 - "1 Arsitektur Perangkat Lunak Tingka Module"
Cohesion: 0.20
Nodes (10): 1 Arsitektur Perangkat Lunak Tingkat Tinggi High-Level Architecture, 21 Sekuensial 1 Transaksi Kasir POS dengan Pembayaran QRIS  Kamera Bukti, 22 Sekuensial 2 Pencatatan Kas Operasional Harian  Saldo Kas Warung, 23 Sekuensial 3 Pipeline Algoritma FP-Growth Data Mining Keranjang Belanja, 2 Diagram Sekuensial Sequence Diagrams, 3 Pemetaan Direktori  Komponen Kode Component Mapping, Arsitektur Sistem  Diagram Sekuensial, Desain Perangkat Lunak  Alur Interaksi Komponen (+2 more)

### Community 39 - "Caching Best Practices Module"
Cohesion: 0.22
Nodes (9): Caching Best Practices, Configure Failover Cache Stores in Production, Consider Cacheflexible for Stale-While-Revalidate, Caching, Use Cache Tags to Invalidate Related Groups, Use Cacheadd for Atomic Conditional Writes, Use Cachememo to Avoid Redundant Hits Within an Execution, Use Cacheremember for Cache-Aside Reads (+1 more)

### Community 40 - "Add Indexes For Measured Query Patt Module"
Cohesion: 0.22
Nodes (9): Add Indexes for Measured Query Patterns, Count Relationships Without Loading Them, Database Performance Best Practices, Db Performance, Eager Load Relationships Before Iterating, Keep Queries Out of Blade Templates, Prevent Lazy Loading in Development, Process Large Data Sets Incrementally (+1 more)

### Community 41 - "Apply Global Scopes Sparingly Module"
Cohesion: 0.22
Nodes (9): Apply Global Scopes Sparingly, Cast Date and Time Attributes, Define Attribute Casts, Define Precise Relationship Types, Eloquent, Eloquent Best Practices, Keep Application Queries Model-Aware, Use Local Scopes for Reusable Queries (+1 more)

### Community 42 - "Require Module"
Cohesion: 0.22
Nodes (9): require, laravel/framework, laravel/tinker, php, phpoffice/phpspreadsheet, 4 Kebutuhan Fungsional Functional Requirements, 5 Kebutuhan Non-Fungsional Non-Functional Requirements, Product Requirements Document PRD (+1 more)

### Community 45 - "App.Php Module"
Cohesion: 0.32
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 46 - "Documentation Files Module"
Cohesion: 0.25
Nodes (8): Documentation Files, Read Environment Variables in Configuration Files, autoload, files, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 47 - "Add Context To Exception Classes Module"
Cohesion: 0.25
Nodes (8): Add Context to Exception Classes, Choose Where to Report and Render Exceptions, Define JSON Rendering for API Routes, Error Handling, Error Handling Best Practices, Mark Exceptions the Handler Should Not Report, Prevent Duplicate Reports of One Exception Instance, Throttle High-Volume Exception Reports

### Community 48 - "Bound Work Inside The Task Module"
Cohesion: 0.25
Nodes (8): Bound Work Inside the Task, Scheduling, Group Shared Configuration, Prevent Unwanted Overlap, Restrict Tasks by Environment, Run a Task on One Server, Run Eligible Commands in the Background, Task Scheduling Best Practices

### Community 49 - "Endpoint Tests Module"
Cohesion: 0.25
Nodes (8): Endpoint Tests, Endpoint Coverage, Endpoint Tests, How to Write the Test, Tenant Isolation, Test Authorization at the Policy Level, Testing Validation, Which Layer Owns Which Case

### Community 50 - "Database Module"
Cohesion: 0.25
Nodes (8): Database, Isolation, Fakes Mocks and Determinism, Framework Fakes, How to Isolate a Dependency, Mocking, Outbound HTTP Testing, Time and Randomness

### Community 52 - "Choose Between Cursor And Lazy Module"
Cohesion: 0.29
Nodes (7): Choose Between cursor and lazy, Collection Best Practices, Collections, Use CollectedBy for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use lazyById When Updating Records While Iterating, Use toQuery for Bulk Operations on Collections

### Community 53 - "Http Client Module"
Cohesion: 0.29
Nodes (7): Http Client, Fake HTTP Requests in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Pool Independent Requests, Retry Only Safe Operations, Set Explicit Timeouts

### Community 54 - "Assert The Delivery Mode Module"
Cohesion: 0.29
Nodes (7): Assert the Delivery Mode, Dispatch Queued Mail After Commit, Mail, Mail Best Practices, Queue Slow Mail Delivery, Separate Content and Delivery Tests, Use Markdown Mailables When They Fit

### Community 55 - "Convention And Style Best Practices Module"
Cohesion: 0.29
Nodes (7): Convention and Style Best Practices, Style, Follow Project Naming Conventions, Keep Presentation Code Maintainable, Prefer Clear Idiomatic Syntax, Use Utilities When They Clarify Intent, Write Comments That Explain Why

### Community 56 - "Arrange Act Assert Module"
Cohesion: 0.29
Nodes (7): Arrange Act Assert, Assert a Known Value, Assert the Complete Result, Assertions, Assertions, How to Find the Correct Assertion, Named Response Assertions

### Community 57 - "Common Errors Module"
Cohesion: 0.29
Nodes (7): Common Errors, Performance, Global Fakes, How to Find a Slow Test, How to Run the Suite in Parallel, Test Environment, Test Suite Performance

### Community 58 - "Assertions Module"
Cohesion: 0.29
Nodes (7): Assertions, Coverage, Data and Determinism, Review, Names and Structure, Reviewing Tests, Test Value

### Community 59 - "A Plaintext Env File Committed To T Module"
Cohesion: 0.33
Nodes (6): A plaintext env file committed to the repository, Configuration Best Practices, Config, Name Repeated Domain Values, Protect Production Secrets, Use Appenvironment for Environment Checks

### Community 60 - "Naming Module"
Cohesion: 0.33
Nodes (6): Naming, File Layout, Grouping, Naming and Structure, Naming Tests, Test Class and Methods

### Community 61 - "Consistency First Module"
Cohesion: 0.33
Nodes (6): Consistency First, Skill, How to Apply, Rule Index, Testing Best Practices, What to Test

### Community 63 - "Data Providers Module"
Cohesion: 0.40
Nodes (5): Data Providers, Test Data, Each Test Makes Its Own Data, Factories and Test Data, Record Construction

### Community 65 - "Daftar Dokumen Perancangan Module"
Cohesion: 0.40
Nodes (5): Daftar Dokumen Perancangan, Readme, Dokumentasi Perancangan Sistem Kobana POS  Analisis FP-Growth, Ringkasan Profil  Arsitektur Sistem, Struktur Cabang  Operasional Bisnis

## Knowledge Gaps
- **451 isolated node(s):** `php`, `layouts.app`, `layouts.app`, `layouts.app`, `layouts.guest` (+446 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 681 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **54 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Transaction` connect `Payment Proof & Camera Tests` to `Database Migrations & Schema Definitions`, `Cache Event Discovery During Produc Module`, `Data Import Commands & Format Helpers`, `Base Test Suites & Unit Testing`, `Category Management & Taxonomy`, `Dashboard Metrics & Chart Analytics`, `Importcontroller.Php Module`, `Illuminate\Foundation\Testing\Testc Module`, `Illuminate\Http\Uploadedfile Module`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **Are the 5 inferred relationships involving `Transaction` (e.g. with `Use ShouldDispatchAfterCommit Inside Transactions` and `35 Tabel transactions Header Penjualan POS`) actually correct?**
  _`Transaction` has 5 INFERRED edges - model-reasoned connections that need verification._
- **What connects `php`, `layouts.app`, `layouts.app` to the rest of the system?**
  _451 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Branch Management & Multi-Store Data` be split into smaller, more focused modules?**
  _Cohesion score 0.05737704918032787 - nodes in this community are weakly interconnected._
- **Why does `User` connect `Composer Dependencies & Autoload Config` to `Database Migrations & Schema Definitions`, `Data Import Commands & Format Helpers`, `POS User Guide & Workflow Documentation`, `Illuminate\Support\Facades\Hash Module`, `Authentication & Password Management`, `POS Transaction & Order Processing`, `Payment Proof & Camera Tests`, `Dashboard Metrics & Chart Analytics`, `Operationalcashtest.Php Module`, `Illuminate\Foundation\Testing\Testc Module`, `Illuminate\Http\Uploadedfile Module`, `Cache.Php Module`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Should `Database Migrations & Schema Definitions` be split into smaller, more focused modules?**
  _Cohesion score 0.07012987012987013 - nodes in this community are weakly interconnected._
- **Why does `Controller` connect `Controller.Php Module` to `Data Import Commands & Format Helpers`, `Fpgrowthcontroller.Php Module`, `Menucontroller Module`, `POS Transaction & Order Processing`, `Base Test Suites & Unit Testing`, `Category Management & Taxonomy`, `Categorycontroller.Php Module`, `Importcontroller.Php Module`, `A Validation  Http Input Module`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._