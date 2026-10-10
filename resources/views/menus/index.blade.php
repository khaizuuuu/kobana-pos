@extends('layouts.app')

@section('title', 'Master Menu')
@section('page-title', 'Master Menu & Produk')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-cup-hot-fill text-danger"></i> Daftar Menu Kobana
        </h6>
        @if(auth()->user()?->role === 'admin')
            <a href="{{ route('menus.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle-fill me-1"></i> Tambah Menu Baru
            </a>
        @endif
    </div>

    <!-- Filter & Search Bar -->
    <div class="card-body border-bottom bg-light py-3">
        <form method="GET" action="{{ route('menus.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau kode menu..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <select name="sort_by" class="form-select form-select-sm">
                    <option value="code" {{ request('sort_by') === 'code' ? 'selected' : '' }}>Urut Kode</option>
                    <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Urut Nama</option>
                    <option value="price" {{ request('sort_by') === 'price' ? 'selected' : '' }}>Urut Harga</option>
                    <option value="transaction_items_count" {{ request('sort_by') === 'transaction_items_count' ? 'selected' : '' }}>Paling Laris</option>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm px-3">Filter</button>
                @if(request()->hasAny(['search', 'category_id', 'status', 'sort_by']))
                    <a href="{{ route('menus.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">Kode</th>
                        <th>Nama Menu</th>
                        <th>Kategori</th>
                        <th>Harga Satuan</th>
                        <th>Total Terjual</th>
                        <th>Status</th>
                        <th style="width: 140px;" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menus as $menu)
                        <tr>
                            <td>
                                <span class="badge bg-dark px-2 py-1">{{ $menu->code }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $menu->name }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $menu->category?->name ?? '-' }}
                                </span>
                            </td>
                            <td class="fw-bold text-success">
                                {{ $menu->formatted_price }}
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                    <i class="bi bi-cart-check-fill me-1"></i> {{ number_format($menu->transaction_items_count, 0, ',', '.') }} kali
                                </span>
                            </td>
                            <td>
                                @if($menu->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                        <i class="bi bi-x-circle-fill me-1"></i> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(auth()->user()?->role === 'admin')
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('menus.edit', $menu) }}" class="btn btn-outline-primary" title="Edit Menu">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <form action="{{ route('menus.toggle-status', $menu) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn {{ $menu->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $menu->is_active ? 'Nonaktifkan Menu' : 'Aktifkan Menu' }}" data-confirm data-confirm-title="Ubah Status Menu" data-confirm-message="Yakin ingin {{ $menu->is_active ? 'menonaktifkan' : 'mengaktifkan' }} menu {{ $menu->name }}?" data-confirm-btn="Ya, Ubah">
                                                <i class="bi {{ $menu->is_active ? 'bi-eye-slash-fill' : 'bi-eye-fill' }}"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('menus.destroy', $menu) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-delete" title="Hapus / Nonaktifkan Menu" data-confirm-delete data-confirm-title="Hapus Menu" data-confirm-message="Yakin ingin menghapus menu {{ $menu->name }}?">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted small">Hanya Lihat</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-cup-hot fs-3 d-block mb-1"></i> Belum ada data menu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($menus->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $menus->links() }}
        </div>
    @endif
</div>
@endsection
