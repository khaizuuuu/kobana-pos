@extends('layouts.app')

@section('title', 'Master Kategori')
@section('page-title', 'Master Kategori Menu')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-tags-fill text-warning"></i> Daftar Kategori
                </h6>
                @if(auth()->user()?->role === 'admin')
                    <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Kategori
                    </a>
                @endif
            </div>

            <div class="card-body border-bottom bg-light py-3">
                <form method="GET" action="{{ route('categories.index') }}" class="row g-2">
                    <div class="col-md-9">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama kategori..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-secondary btn-sm flex-fill">Cari</button>
                        @if(request()->filled('search'))
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Nama Kategori</th>
                                <th class="text-center">Jumlah Menu</th>
                                <th class="text-end" style="width: 130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $index => $cat)
                                <tr>
                                    <td class="text-muted small">{{ $categories->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $cat->name }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary bg-opacity-10 text-dark border px-2 py-1">
                                            {{ $cat->menus_count }} menu
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if(auth()->user()?->role === 'admin')
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('categories.edit', $cat) }}" class="btn btn-outline-primary" title="Edit Kategori">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-delete" title="Hapus Kategori" data-confirm-delete data-confirm-title="Hapus Kategori" data-confirm-message="Yakin ingin menghapus kategori {{ $cat->name }}?">
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
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-tags fs-3 d-block mb-1"></i> Belum ada kategori menu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($categories->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
