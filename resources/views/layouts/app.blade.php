<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Kobana Fried Chicken</title>

    <!-- Bootstrap 5 & Bootstrap Icons via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar Desktop -->
        <div id="sidebar-wrapper" class="d-none d-lg-block">
            <div class="brand-section">
                <div class="brand-title">
                    <i class="bi bi-fire text-warning"></i>
                    <span>Kobana POS</span>
                    <span class="brand-badge ms-auto">v1.0</span>
                </div>
            </div>
            
            <div class="sidebar-menu">
                <div class="sidebar-heading">Utama</div>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>

                <div class="sidebar-heading">Transaksi</div>
                @if(in_array(auth()->user()?->role, ['admin', 'kasir']))
                <a href="{{ route('transactions.create') }}" class="nav-link {{ request()->routeIs('transactions.create') ? 'active' : '' }}">
                    <i class="bi bi-cart-plus-fill"></i>
                    <span>Kasir / Input Transaksi</span>
                </a>
                @endif
                <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.index') || request()->routeIs('transactions.show') || request()->routeIs('transactions.edit') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span>Riwayat Transaksi</span>
                </a>

                <div class="sidebar-heading">Master Data</div>
                <a href="{{ route('menus.index') }}" class="nav-link {{ request()->routeIs('menus.*') ? 'active' : '' }}">
                    <i class="bi bi-cup-hot-fill"></i>
                    <span>Master Menu</span>
                </a>
                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags-fill"></i>
                    <span>Master Kategori</span>
                </a>

                <div class="sidebar-heading">Analisis & Laporan</div>
                @if(in_array(auth()->user()?->role, ['admin', 'owner']))
                <a href="{{ route('analysis.fp-growth') }}" class="nav-link {{ request()->routeIs('analysis.*') ? 'active' : '' }}">
                    <i class="bi bi-diagram-3-fill"></i>
                    <span>Analisis FP-Growth</span>
                </a>
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span>Laporan Penjualan</span>
                </a>
                @endif

                @if(auth()->user()?->role === 'admin')
                <a href="{{ route('imports.index') }}" class="nav-link {{ request()->routeIs('imports.*') ? 'active' : '' }}">
                    <i class="bi bi-cloud-arrow-up-fill"></i>
                    <span>Impor Dataset</span>
                </a>
                <div class="sidebar-heading">Sistem</div>
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Manajemen User</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Sidebar Mobile (Offcanvas) -->
        <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="offcanvasSidebar">
            <div class="offcanvas-header brand-section">
                <h5 class="offcanvas-title brand-title">
                    <i class="bi bi-fire text-warning"></i> Kobana POS
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body sidebar-menu p-2">
                <div class="sidebar-heading">Utama</div>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>

                <div class="sidebar-heading">Transaksi</div>
                @if(in_array(auth()->user()?->role, ['admin', 'kasir']))
                <a href="{{ route('transactions.create') }}" class="nav-link {{ request()->routeIs('transactions.create') ? 'active' : '' }}">
                    <i class="bi bi-cart-plus-fill"></i> Kasir / Input
                </a>
                @endif
                <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.index') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i> Riwayat Transaksi
                </a>

                <div class="sidebar-heading">Master Data</div>
                <a href="{{ route('menus.index') }}" class="nav-link {{ request()->routeIs('menus.*') ? 'active' : '' }}">
                    <i class="bi bi-cup-hot-fill"></i> Master Menu
                </a>
                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags-fill"></i> Master Kategori
                </a>

                <div class="sidebar-heading">Analisis & Laporan</div>
                @if(in_array(auth()->user()?->role, ['admin', 'owner']))
                <a href="{{ route('analysis.fp-growth') }}" class="nav-link {{ request()->routeIs('analysis.*') ? 'active' : '' }}">
                    <i class="bi bi-diagram-3-fill"></i> Analisis FP-Growth
                </a>
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i> Laporan Penjualan
                </a>
                @endif

                @if(auth()->user()?->role === 'admin')
                <a href="{{ route('imports.index') }}" class="nav-link {{ request()->routeIs('imports.*') ? 'active' : '' }}">
                    <i class="bi bi-cloud-arrow-up-fill"></i> Impor Dataset
                </a>
                <div class="sidebar-heading">Sistem</div>
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Manajemen User
                </a>
                @endif
            </div>
        </div>

        <!-- Page Content Wrapper -->
        <div id="page-content-wrapper">
            <!-- Top Navbar -->
            <nav class="navbar main-navbar d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <h5 class="m-0 fw-bold text-dark">@yield('page-title', 'Kobana Fried Chicken')</h5>
                </div>

                <div class="d-flex align-items-center gap-3">
                    @auth
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border" type="button" data-bs-toggle="dropdown">
                                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="text-start d-none d-sm-block">
                                    <div class="fw-semibold lh-1" style="font-size: 0.85rem;">{{ auth()->user()->name }}</div>
                                    <span class="badge badge-role-{{ auth()->user()->role }} mt-1" style="font-size: 0.65rem;">
                                        {{ ucfirst(auth()->user()->role) }}
                                    </span>
                                </div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li class="dropdown-header">Login sebagai <strong>{{ auth()->user()->email }}</strong></li>
                                <li><a class="dropdown-item" href="{{ route('profile.password') }}"><i class="bi bi-key me-2"></i> Ganti Password</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger" data-confirm data-confirm-title="Konfirmasi Keluar" data-confirm-message="Apakah Anda yakin ingin keluar dari sistem?" data-confirm-btn="Ya, Logout">
                                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                    @endauth
                </div>
            </nav>

            <!-- Main Content Body -->
            <main class="main-container">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-top py-3 px-4 text-center text-muted" style="font-size: 0.85rem;">
                &copy; {{ date('Y') }} Kobana Fried Chicken — Sistem Manajemen Data & Market Basket Analysis (FP-Growth)
            </footer>
        </div>
    </div>

    <!-- Scripts via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    @include('partials.swal')
    @stack('scripts')
</body>
</html>
