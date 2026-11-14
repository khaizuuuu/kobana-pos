# Graph Report - antigravity  (2026-11-14)

## Corpus Check
- 149 files · ~69,038 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 21 file(s) not represented in the graph (top: (none) 16, .css 2, .example 1)

## Summary
- 1006 nodes · 1467 edges · 102 communities (43 shown, 59 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 65 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Branch Management & Multi-Store Data
- Database Migrations & Schema Definitions
- Console CLI & Artisan Application
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
- A Validation  Http Input Module
- Skill Module
- Apply Cross-Site Request Forgery Pr Module
- Cache.Php Module
- Dashboardcontroller Module
- Importcontroller.Php Module
- Controller.Php Module
- Config Module
- Advanced Query Best Practices Module
- Cache Event Discovery During Produc Module
- Back Off Transient Failures Module
- Basic Usage Module
- Caching Best Practices Module
- Add Indexes For Measured Query Patt Module
- Apply Global Scopes Sparingly Module
- Fpgrowthcontroller.Php Module
- App.Php Module
- Documentation Files Module
- Add Context To Exception Classes Module
- Bound Work Inside The Task Module
- Endpoint Tests Module
- Database Module
- Authandusermanagementtest Module
- Choose Between Cursor And Lazy Module
- Http Client Module
- Assert The Delivery Mode Module
- Routing Module
- Convention And Style Best Practices Module
- Arrange Act Assert Module
- Common Errors Module
- Assertions Module
- Operationalcashtest Module
- A Plaintext Env File Committed To T Module
- Naming Module
- Consistency First Module
- Dashboardanalyticstest Module
- Reporttest Module
- Rolemiddleware.Php Module
- Data Providers Module
- Sidebarfeaturestest Module
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
2. `Panduan Penggunaan` - 32 edges
3. `Transaction` - 31 edges
4. `Readme` - 30 edges
5. `Menu` - 28 edges
6. `Claude` - 25 edges
7. `Kobana Prd Erd Prompt` - 23 edges
8. `Skill` - 22 edges
9. `Category` - 21 edges
10. `TestCase` - 20 edges

## Surprising Connections (you probably didn't know these)
- `B Controllers  routing` --conceptually_related_to--> `Controller`  [INFERRED]
  .claude/skills/infer-conventions/references/checklist.md → app/Http/Controllers/Controller.php
- `Keep Controllers Focused on HTTP Concerns` --conceptually_related_to--> `Controller`  [INFERRED]
  .claude/skills/laravel-best-practices/rules/routing.md → app/Http/Controllers/Controller.php
- `Organize Controllers Around Resources` --conceptually_related_to--> `Controller`  [INFERRED]
  .claude/skills/laravel-best-practices/rules/routing.md → app/Http/Controllers/Controller.php
- `Routing and Controller Best Practices` --conceptually_related_to--> `Controller`  [INFERRED]
  .claude/skills/laravel-best-practices/rules/routing.md → app/Http/Controllers/Controller.php
- `Use ShouldDispatchAfterCommit Inside Transactions` --conceptually_related_to--> `Transaction`  [INFERRED]
  .claude/skills/laravel-best-practices/rules/events-notifications.md → app/Models/Transaction.php

## Import Cycles
- None detected.

## Communities (102 total, 59 thin omitted)

### Community 0 - "Branch Management & Multi-Store Data"
Cohesion: 0.06
Nodes (7): Branch, FpAnalysis, FpAssociationRule, FpFrequentItemset, ImportLog, OperationalCash, TransactionItem

### Community 1 - "Database Migrations & Schema Definitions"
Cohesion: 0.06
Nodes (25): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+17 more)

### Community 2 - "Console CLI & Artisan Application"
Cohesion: 0.04
Nodes (46): APIs  Eloquent Resources, Application Structure  Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Claude, Foundational Context (+38 more)

### Community 3 - "Architecture Principles & Best Practices"
Cohesion: 0.04
Nodes (49): Architecture Best Practices, Depend on Contracts at Boundaries, Architecture, Extract Focused Business Operations, Follow Framework Conventions, Inject Required Dependencies, Specify a Deterministic Sort Order, Use Atomic Locks for Race Conditions (+41 more)

### Community 4 - "Data Import Commands & Format Helpers"
Cohesion: 0.08
Nodes (7): ImportDatasetCommand, format_rupiah(), format_tanggal(), nama_hari(), ProcessDatasetImportJob, RunFpGrowthJob, DatasetImportService

### Community 5 - "POS User Guide & Workflow Documentation"
Cohesion: 0.06
Nodes (33): 10 Manajemen Pengguna User  Profil, 11 Tips  Troubleshooting, 1 Akses Sistem  Akun Pengguna, 2 Dashboard Analitik, 3 Kasir  Input Transaksi Baru POS, 4 Riwayat  Detail Transaksi, 5 Kas  Belanja Harian Warung Petty Cash  Tabungan, 6 Master Menu  Master Kategori (+25 more)

### Community 6 - "Blade Components & UI Views"
Cohesion: 0.08
Nodes (30): Build and Deploy, Blade and View Best Practices, Blade Views, Prefer Components for Explicit Interfaces, Return Blade Fragments for Partial Rendering, Share Compatible View Data with a View Composer, Share Parent Component Props with aware, Use attributes-merge in Component Templates (+22 more)

### Community 7 - "Agent Setup & Configuration Guidelines"
Cohesion: 0.07
Nodes (31): Agent Setup, Agents, Laravel Application, Prerequisites, Adding a cache to an existing environment, Adding a database to an existing environment, Checklists for Multi-Step Operations, Custom domain setup (+23 more)

### Community 8 - "Installation & Deployment Procedures"
Cohesion: 0.06
Nodes (31): 1 Clone  ekstrak repositori, 2 Install dependency PHP, 3 Salin file environment, 4 Generate application key, 5 Jalankan migrasi  seeder, 6 Opsional Jalankan development server, Algoritma FP-Growth, Analisis FP-Growth analysisfp-growth admin  owner (+23 more)

### Community 9 - "FP-Growth Algorithm & Market Basket Analysis"
Cohesion: 0.13
Nodes (5): FpGrowthService, FpTreeNode, DatabaseSeeder, DummyDataSeeder, FpGrowthServiceTest

### Community 10 - "Composer Dependencies & Autoload Config"
Cohesion: 0.07
Nodes (28): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+20 more)

### Community 11 - "User Model & Role Authorization"
Cohesion: 0.12
Nodes (10): User, AppServiceProvider, {closure#10}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}() (+2 more)

### Community 14 - "POS Transaction & Order Processing"
Cohesion: 0.17
Nodes (3): {closure#1}(), {closure#4}(), Transaction

### Community 15 - "Base Test Suites & Unit Testing"
Cohesion: 0.19
Nodes (3): ExampleTest, MasterDataTest, TestCase

### Community 20 - "A Validation  Http Input Module"
Cohesion: 0.17
Nodes (12): A Validation  HTTP input, B Controllers  routing, C Authorization, D Eloquent  models, Detection Checklist, Checklist, E Architecture  organization, F Frontend  views (+4 more)

### Community 21 - "Skill Module"
Cohesion: 0.17
Nodes (12): Skill, Edge cases, Glob mapping, Ground Rules read before you start, Infer Conventions, Process, Step 0 Orient, Step 1 Predefined sweep (+4 more)

### Community 22 - "Apply Cross-Site Request Forgery Pr Module"
Cohesion: 0.17
Nodes (12): Apply Cross-Site Request Forgery Protection, Audit Dependencies, Authorize Protected Actions, Bind Query Parameters, Control Mass Assignment, Security, Encrypt Sensitive Attributes When Appropriate, Escape Output in Its Context (+4 more)

### Community 27 - "Config Module"
Cohesion: 0.20
Nodes (10): Config, Configuration and Resources, CSS-First Configuration, pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader (+2 more)

### Community 28 - "Advanced Query Best Practices Module"
Cohesion: 0.20
Nodes (10): Advanced Query Best Practices, Combine Related Counts with Conditional Aggregates, Compare whereHas with an IN Subquery, Consider a Correlated Subquery for Has-Many Ordering, Create Dynamic Relationships with a Subquery Foreign Key, Design Composite Indexes for the Query, Advanced Queries, Measure Two Simple Queries Against One Complex Query (+2 more)

### Community 29 - "Cache Event Discovery During Produc Module"
Cohesion: 0.20
Nodes (10): Cache Event Discovery During Production Deployment, Dispatch Queued Notifications After Commit, Events Notifications, Events and Notifications Best Practices, Implement HasLocalePreference on Notifiable Models, Queue Slow Notifications, Rely on Event Discovery, Route Notification Channels to Dedicated Queues (+2 more)

### Community 30 - "Back Off Transient Failures Module"
Cohesion: 0.20
Nodes (10): Back Off Transient Failures, Batch Jobs for Group Coordination, Configure Time-Based Retry Limits Deliberately, Queue Jobs, Handle Terminal Failure When Needed, Keep Reservation Time Longer Than Execution Time, Queue and Job Best Practices, Rate Limit External Calls (+2 more)

### Community 31 - "Basic Usage Module"
Cohesion: 0.20
Nodes (10): Basic Usage, Common Pitfalls, Dark Mode, Skill, Documentation, Import Syntax, Replaced Utilities, Spacing (+2 more)

### Community 32 - "Caching Best Practices Module"
Cohesion: 0.22
Nodes (9): Caching Best Practices, Configure Failover Cache Stores in Production, Consider Cacheflexible for Stale-While-Revalidate, Caching, Use Cache Tags to Invalidate Related Groups, Use Cacheadd for Atomic Conditional Writes, Use Cachememo to Avoid Redundant Hits Within an Execution, Use Cacheremember for Cache-Aside Reads (+1 more)

### Community 33 - "Add Indexes For Measured Query Patt Module"
Cohesion: 0.22
Nodes (9): Add Indexes for Measured Query Patterns, Count Relationships Without Loading Them, Database Performance Best Practices, Db Performance, Eager Load Relationships Before Iterating, Keep Queries Out of Blade Templates, Prevent Lazy Loading in Development, Process Large Data Sets Incrementally (+1 more)

### Community 34 - "Apply Global Scopes Sparingly Module"
Cohesion: 0.22
Nodes (9): Apply Global Scopes Sparingly, Cast Date and Time Attributes, Define Attribute Casts, Define Precise Relationship Types, Eloquent, Eloquent Best Practices, Keep Application Queries Model-Aware, Use Local Scopes for Reusable Queries (+1 more)

### Community 36 - "App.Php Module"
Cohesion: 0.32
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 37 - "Documentation Files Module"
Cohesion: 0.25
Nodes (8): Documentation Files, Read Environment Variables in Configuration Files, autoload, files, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 38 - "Add Context To Exception Classes Module"
Cohesion: 0.25
Nodes (8): Add Context to Exception Classes, Choose Where to Report and Render Exceptions, Define JSON Rendering for API Routes, Error Handling, Error Handling Best Practices, Mark Exceptions the Handler Should Not Report, Prevent Duplicate Reports of One Exception Instance, Throttle High-Volume Exception Reports

### Community 39 - "Bound Work Inside The Task Module"
Cohesion: 0.25
Nodes (8): Bound Work Inside the Task, Scheduling, Group Shared Configuration, Prevent Unwanted Overlap, Restrict Tasks by Environment, Run a Task on One Server, Run Eligible Commands in the Background, Task Scheduling Best Practices

### Community 40 - "Endpoint Tests Module"
Cohesion: 0.25
Nodes (8): Endpoint Tests, Endpoint Coverage, Endpoint Tests, How to Write the Test, Tenant Isolation, Test Authorization at the Policy Level, Testing Validation, Which Layer Owns Which Case

### Community 41 - "Database Module"
Cohesion: 0.25
Nodes (8): Database, Isolation, Fakes Mocks and Determinism, Framework Fakes, How to Isolate a Dependency, Mocking, Outbound HTTP Testing, Time and Randomness

### Community 43 - "Choose Between Cursor And Lazy Module"
Cohesion: 0.29
Nodes (7): Choose Between cursor and lazy, Collection Best Practices, Collections, Use CollectedBy for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use lazyById When Updating Records While Iterating, Use toQuery for Bulk Operations on Collections

### Community 44 - "Http Client Module"
Cohesion: 0.29
Nodes (7): Http Client, Fake HTTP Requests in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Pool Independent Requests, Retry Only Safe Operations, Set Explicit Timeouts

### Community 45 - "Assert The Delivery Mode Module"
Cohesion: 0.29
Nodes (7): Assert the Delivery Mode, Dispatch Queued Mail After Commit, Mail, Mail Best Practices, Queue Slow Mail Delivery, Separate Content and Delivery Tests, Use Markdown Mailables When They Fit

### Community 46 - "Routing Module"
Cohesion: 0.29
Nodes (7): Routing, Keep Controllers Focused on HTTP Concerns, Organize Controllers Around Resources, Routing and Controller Best Practices, Scope Nested Bindings, Use Implicit Route Model Binding, Use Resource Routes for Resourceful Actions

### Community 47 - "Convention And Style Best Practices Module"
Cohesion: 0.29
Nodes (7): Convention and Style Best Practices, Style, Follow Project Naming Conventions, Keep Presentation Code Maintainable, Prefer Clear Idiomatic Syntax, Use Utilities When They Clarify Intent, Write Comments That Explain Why

### Community 48 - "Arrange Act Assert Module"
Cohesion: 0.29
Nodes (7): Arrange Act Assert, Assert a Known Value, Assert the Complete Result, Assertions, Assertions, How to Find the Correct Assertion, Named Response Assertions

### Community 49 - "Common Errors Module"
Cohesion: 0.29
Nodes (7): Common Errors, Performance, Global Fakes, How to Find a Slow Test, How to Run the Suite in Parallel, Test Environment, Test Suite Performance

### Community 50 - "Assertions Module"
Cohesion: 0.29
Nodes (7): Assertions, Coverage, Data and Determinism, Review, Names and Structure, Reviewing Tests, Test Value

### Community 52 - "A Plaintext Env File Committed To T Module"
Cohesion: 0.33
Nodes (6): A plaintext env file committed to the repository, Configuration Best Practices, Config, Name Repeated Domain Values, Protect Production Secrets, Use Appenvironment for Environment Checks

### Community 53 - "Naming Module"
Cohesion: 0.33
Nodes (6): Naming, File Layout, Grouping, Naming and Structure, Naming Tests, Test Class and Methods

### Community 54 - "Consistency First Module"
Cohesion: 0.33
Nodes (6): Consistency First, Skill, How to Apply, Rule Index, Testing Best Practices, What to Test

### Community 58 - "Data Providers Module"
Cohesion: 0.40
Nodes (5): Data Providers, Test Data, Each Test Makes Its Own Data, Factories and Test Data, Record Construction

## Knowledge Gaps
- **363 isolated node(s):** `php`, `layouts.app`, `layouts.app`, `layouts.app`, `layouts.guest` (+358 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 593 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **59 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Transaction` connect `POS Transaction & Order Processing` to `Branch Management & Multi-Store Data`, `Data Import Commands & Format Helpers`, `Installation & Deployment Procedures`, `Operational Cash & Petty Expenses`, `Base Test Suites & Unit Testing`, `Category Management & Taxonomy`, `Payment Proof & Camera Tests`, `Dashboard Metrics & Chart Analytics`, `Importcontroller.Php Module`, `Controller.Php Module`, `Sidebarfeaturestest Module`, `Cache Event Discovery During Produc Module`?**
  _High betweenness centrality (0.057) - this node is a cross-community bridge._
- **What connects `php`, `layouts.app`, `layouts.app` to the rest of the system?**
  _363 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Branch Management & Multi-Store Data` be split into smaller, more focused modules?**
  _Cohesion score 0.060814383923849816 - nodes in this community are weakly interconnected._
- **Why does `Claude` connect `Console CLI & Artisan Application` to `Documentation Files Module`, `Blade Components & UI Views`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._
- **Should `Database Migrations & Schema Definitions` be split into smaller, more focused modules?**
  _Cohesion score 0.05737704918032787 - nodes in this community are weakly interconnected._
- **Why does `config` connect `Config Module` to `Caching Best Practices Module`, `Documentation Files Module`, `Bound Work Inside The Task Module`, `Composer Dependencies & Autoload Config`, `A Plaintext Env File Committed To T Module`, `Back Off Transient Failures Module`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **Should `Console CLI & Artisan Application` be split into smaller, more focused modules?**
  _Cohesion score 0.04336734693877551 - nodes in this community are weakly interconnected._