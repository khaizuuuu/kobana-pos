@extends('layouts.app')

@section('title', 'Edit Transaksi ' . $transaction->transaction_code)
@section('page-title', 'Edit Transaksi: ' . $transaction->transaction_code)

@section('content')
<form id="transaction-form" method="POST" action="{{ route('transactions.update', $transaction) }}">
    @csrf
    @method('PUT')
    <input type="hidden" name="cart" id="cart-input" value="[]" />

    <div class="row g-4">
        <!-- Daftar Menu -->
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-cup-hot-fill text-danger"></i> Pilih Menu
                    </h6>
                    <div class="input-group input-group-sm" style="max-width: 240px;">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" id="menu-search" class="form-control" placeholder="Cari nama/kode menu...">
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" id="menu-table">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 75px;">Kode</th>
                                    <th>Menu</th>
                                    <th>Harga</th>
                                    <th style="width: 80px;" class="text-center">Qty</th>
                                    <th style="width: 100px;" class="text-center">Diskon</th>
                                    <th style="width: 90px;" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($menus as $menu)
                                    <tr class="menu-row" data-id="{{ $menu->id }}" data-code="{{ $menu->code }}" data-name="{{ $menu->name }}" data-price="{{ $menu->price }}">
                                        <td><span class="badge bg-dark">{{ $menu->code }}</span></td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $menu->name }}</div>
                                            <small class="text-muted">{{ $menu->category?->name ?? 'Menu' }}</small>
                                        </td>
                                        <td class="fw-bold text-success">@rupiah($menu->price)</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm text-center item-qty" value="1" min="1" max="999">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm text-end item-discount" value="0" min="0" step="500" placeholder="Rp">
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-primary btn-add-item">
                                                <i class="bi bi-plus-lg"></i> Tambah
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            Tidak ada master menu.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keranjang Belanja -->
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-cart3 text-primary"></i> Keranjang Transaksi (Edit)
                    </h6>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btn-clear-cart">
                        <i class="bi bi-trash"></i> Kosongkan
                    </button>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 340px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" id="cart-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Menu</th>
                                    <th class="text-center" style="width: 70px;">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                    <th style="width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="cart-items-body">
                                <tr id="empty-cart-row">
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-cart-x fs-1 d-block mb-2 opacity-50"></i>
                                        Keranjang masih kosong.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light border-top p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Jumlah Item:</span>
                        <span class="fw-bold fs-6 text-dark" id="summary-total-items">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold text-dark fs-6">Total Pembayaran:</span>
                        <span class="fw-bold fs-4 text-success" id="summary-total-amount">Rp 0</span>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-secondary flex-fill">
                            Batal
                        </a>
                        <button type="button" class="btn btn-primary flex-fill fw-semibold shadow-sm" id="btn-submit-order">
                            <i class="bi bi-check2 me-1"></i> Update Transaksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let cart = @json($existingItems ?? []);

    const formatRupiah = (val) => 'Rp ' + Number(val).toLocaleString('id-ID', { minimumFractionDigits: 0 });

    const cartInput = document.getElementById('cart-input');
    const cartBody = document.getElementById('cart-items-body');
    const emptyRow = document.getElementById('empty-cart-row');
    const totalItemsEl = document.getElementById('summary-total-items');
    const totalAmountEl = document.getElementById('summary-total-amount');
    const submitBtn = document.getElementById('btn-submit-order');
    const clearBtn = document.getElementById('btn-clear-cart');
    const menuSearch = document.getElementById('menu-search');

    function renderCart() {
        if (cart.length === 0) {
            cartBody.innerHTML = `
                <tr id="empty-cart-row">
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="bi bi-cart-x fs-1 d-block mb-2 opacity-50"></i>
                        Keranjang masih kosong.<br><small>Pilih menu di samping untuk menambahkan.</small>
                    </td>
                </tr>`;
            totalItemsEl.textContent = '0';
            totalAmountEl.textContent = 'Rp 0';
            submitBtn.disabled = true;
            clearBtn.style.display = 'none';
            cartInput.value = '[]';
            return;
        }

        let totalQty = 0;
        let totalSum = 0;
        let html = '';

        cart.forEach((item, idx) => {
            totalQty += item.qty;
            totalSum += item.subtotal;

            html += `
                <tr>
                    <td>
                        <div class="fw-semibold text-dark">${item.name}</div>
                        <small class="text-muted">${formatRupiah(item.price)} x ${item.qty} ${item.discount > 0 ? '<span class="text-danger">(Disc ' + formatRupiah(item.discount) + ')</span>' : ''}</small>
                    </td>
                    <td class="text-center">
                        <div class="input-group input-group-sm">
                            <button type="button" class="btn btn-outline-secondary btn-qty-dec px-2" data-idx="${idx}">-</button>
                            <span class="form-control text-center px-1 fw-semibold bg-white">${item.qty}</span>
                            <button type="button" class="btn btn-outline-secondary btn-qty-inc px-2" data-idx="${idx}">+</button>
                        </div>
                    </td>
                    <td class="text-end fw-bold text-dark">${formatRupiah(item.subtotal)}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-item" data-idx="${idx}" title="Hapus item">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </button>
                    </td>
                </tr>`;
        });

        cartBody.innerHTML = html;
        totalItemsEl.textContent = totalQty;
        totalAmountEl.textContent = formatRupiah(totalSum);
        submitBtn.disabled = false;
        clearBtn.style.display = 'inline-block';
        cartInput.value = JSON.stringify(cart);
    }

    renderCart();

    // Add item to cart
    document.querySelectorAll('.btn-add-item').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('.menu-row');
            const menuId = parseInt(row.dataset.id);
            const name = row.dataset.name;
            const price = parseInt(row.dataset.price);
            const qtyInput = row.querySelector('.item-qty');
            const discountInput = row.querySelector('.item-discount');

            const qty = Math.max(1, parseInt(qtyInput.value) || 1);
            const discount = Math.max(0, parseInt(discountInput.value) || 0);

            let subtotal = (price * qty) - discount;
            if (subtotal < 0) subtotal = 0;

            const existingIdx = cart.findIndex(i => i.menu_id === menuId);
            if (existingIdx >= 0) {
                cart[existingIdx].qty += qty;
                cart[existingIdx].discount += discount;
                cart[existingIdx].subtotal = Math.max(0, (price * cart[existingIdx].qty) - cart[existingIdx].discount);
            } else {
                cart.push({
                    menu_id: menuId,
                    name: name,
                    price: price,
                    qty: qty,
                    discount: discount,
                    subtotal: subtotal
                });
            }

            qtyInput.value = 1;
            discountInput.value = 0;

            renderCart();
        });
    });

    // Cart table events
    cartBody.addEventListener('click', function (e) {
        const decBtn = e.target.closest('.btn-qty-dec');
        const incBtn = e.target.closest('.btn-qty-inc');
        const remBtn = e.target.closest('.btn-remove-item');

        if (decBtn) {
            const idx = parseInt(decBtn.dataset.idx);
            if (cart[idx].qty > 1) {
                cart[idx].qty -= 1;
                cart[idx].subtotal = Math.max(0, (cart[idx].price * cart[idx].qty) - cart[idx].discount);
            } else {
                cart.splice(idx, 1);
            }
            renderCart();
        } else if (incBtn) {
            const idx = parseInt(incBtn.dataset.idx);
            cart[idx].qty += 1;
            cart[idx].subtotal = Math.max(0, (cart[idx].price * cart[idx].qty) - cart[idx].discount);
            renderCart();
        } else if (remBtn) {
            const idx = parseInt(remBtn.dataset.idx);
            cart.splice(idx, 1);
            renderCart();
        }
    });

    // Clear cart
    clearBtn.addEventListener('click', function () {
        if (confirm('Kosongkan semua item di keranjang?')) {
            cart = [];
            renderCart();
        }
    });

    // Filter menu search
    menuSearch.addEventListener('input', function () {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('#menu-table tbody .menu-row').forEach(row => {
            const name = (row.dataset.name || '').toLowerCase();
            const code = (row.dataset.code || '').toLowerCase();
            if (name.includes(term) || code.includes(term)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Submit order confirmation
    submitBtn.addEventListener('click', function () {
        if (cart.length === 0) return;
        const totalSum = totalAmountEl.textContent;
        const totalItems = totalItemsEl.textContent;

        if (window.Swal) {
            Swal.fire({
                title: 'Konfirmasi Update Transaksi',
                html: `Simpan perubahan transaksi dengan <b>${totalItems} item</b> senilai <b>${totalSum}</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-check2"></i> Ya, Update Transaksi',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('transaction-form').submit();
                }
            });
        } else {
            if (confirm(`Simpan perubahan transaksi dengan ${totalItems} item senilai ${totalSum}?`)) {
                document.getElementById('transaction-form').submit();
            }
        }
    });
});
</script>
@endpush
