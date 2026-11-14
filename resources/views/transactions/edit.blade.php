@extends('layouts.app')

@section('title', 'Edit Transaksi ' . $transaction->transaction_code)
@section('page-title', 'Edit Transaksi: ' . $transaction->transaction_code)

@section('content')
<form id="transaction-form" method="POST" action="{{ route('transactions.update', $transaction) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" name="cart" id="cart-input" value="[]" />

    <!-- Bar Informasi Cabang & Kasir -->
    <div class="card shadow-sm mb-4 border-start border-4 border-danger">
        <div class="card-body py-2 px-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger p-2"><i class="bi bi-shop fs-6"></i></span>
                <div class="d-flex align-items-center gap-2">
                    <label for="branch_select" class="fw-bold text-dark small mb-0 text-nowrap">Cabang Transaksi:</label>
                    <select name="branch_id" id="branch_select" class="form-select form-select-sm" style="min-width: 220px;">
                        <option value="">-- Tanpa Cabang (Pusat / Global) --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id', $transaction->branch_id) == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="text-md-end small text-muted">
                Kasir Pembuat: <strong class="text-dark">{{ $transaction->creator?->name ?? 'Kasir' }}</strong>
            </div>
        </div>
    </div>

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

                    <!-- Pilihan Metode Pembayaran -->
                    <div class="mb-3 border rounded-3 p-3 bg-white">
                        <label class="form-label fw-bold small text-dark d-block mb-2">
                            <i class="bi bi-credit-card-2-front text-primary me-1"></i>Metode Pembayaran
                        </label>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="payment_method" id="pay-cash" value="cash" {{ old('payment_method', $transaction->payment_method ?? 'cash') === 'cash' ? 'checked' : '' }} autocomplete="off">
                                <label class="btn btn-outline-success btn-sm w-100 py-2 text-center" for="pay-cash">
                                    <i class="bi bi-cash-stack d-block fs-5 mb-1"></i>
                                    <span class="small fw-semibold">Cash (Tunai)</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="payment_method" id="pay-qris" value="qris" {{ old('payment_method', $transaction->payment_method) === 'qris' ? 'checked' : '' }} autocomplete="off">
                                <label class="btn btn-outline-primary btn-sm w-100 py-2 text-center" for="pay-qris">
                                    <i class="bi bi-qr-code-scan d-block fs-5 mb-1"></i>
                                    <span class="small fw-semibold">QRIS</span>
                                </label>
                            </div>
                        </div>

                        <!-- Opsi Pembayaran Tunai (Cash) -->
                        <div id="cash-panel" style="{{ old('payment_method', $transaction->payment_method ?? 'cash') === 'cash' ? '' : 'display: none;' }}">
                            <label class="form-label small fw-semibold text-muted mb-1">Uang Tunai Diterima (Rp)</label>
                            <input type="number" name="cash_amount" id="cash_input" class="form-control form-control-sm mb-2 fw-bold" value="{{ old('cash_amount', (int)$transaction->cash_amount) }}" placeholder="Contoh: 50000" min="0">
                            <div class="d-flex gap-1 mb-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 small quick-cash-btn" data-val="exact">Uang Pas</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 small quick-cash-btn" data-val="20000">20rb</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 small quick-cash-btn" data-val="50000">50rb</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 small quick-cash-btn" data-val="100000">100rb</button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border">
                                <span class="small text-muted fw-semibold">Kembalian:</span>
                                <span class="fw-bold text-primary fs-6" id="change-display">Rp {{ number_format($transaction->change_amount ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Opsi QRIS -->
                        <div id="qris-panel" style="{{ old('payment_method', $transaction->payment_method) === 'qris' ? '' : 'display: none;' }}">
                            <div class="alert alert-primary py-2 px-3 small mb-2">
                                <i class="bi bi-qr-code me-1"></i>
                                <strong>Scan QRIS Kobana</strong><br>
                                Tunjukkan QR Code fisik/layar ke pelanggan dan pastikan notifikasi pembayaran telah berhasil.
                            </div>
                            <input type="text" name="payment_note" id="qris_ref" class="form-control form-control-sm mb-2" value="{{ old('payment_note', $transaction->payment_method === 'qris' ? $transaction->payment_note : '') }}" placeholder="No. Referensi / RRN QRIS (opsional)">

                            <!-- Bukti Foto QRIS (Kamera / File) -->
                            <div class="border rounded-2 p-2 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-bold text-dark">
                                        <i class="bi bi-camera me-1 text-primary"></i> Foto Bukti QRIS
                                    </span>
                                    <span class="badge bg-secondary-subtle text-muted" style="font-size: 10px;">Opsional</span>
                                </div>

                                <!-- Camera Streaming Container -->
                                <div id="camera-stream-box" class="mb-2 text-center" style="display: none;">
                                    <video id="camera-video" playsinline autoplay muted class="w-100 rounded border bg-dark" style="max-height: 200px; object-fit: cover;"></video>
                                    <canvas id="camera-canvas" class="d-none"></canvas>
                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button" class="btn btn-sm btn-primary" id="btn-snap-photo">
                                            <i class="bi bi-camera-fill me-1"></i> Ambil Foto
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-stop-camera">
                                            Batal
                                        </button>
                                    </div>
                                </div>

                                <!-- Buttons to Trigger Camera / Upload -->
                                <div id="camera-buttons-group" class="d-flex gap-2 mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary flex-fill" id="btn-open-camera">
                                        <i class="bi bi-camera-fill me-1"></i> Buka Kamera
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" id="btn-pick-file">
                                        <i class="bi bi-upload me-1"></i> Unggah File
                                    </button>
                                </div>

                                <!-- Inputs (Hidden) -->
                                <input type="hidden" name="payment_proof_camera" id="payment_proof_camera">
                                <input type="file" name="payment_proof_file" id="payment_proof_file" class="d-none" accept="image/*" capture="environment">
                                <input type="hidden" name="remove_payment_proof" id="remove_payment_proof" value="0">

                                <!-- Alert Feedback -->
                                <div id="camera-error-msg" class="alert alert-warning py-1 px-2 small mb-2" style="display: none;"></div>

                                <!-- Preview Image Box -->
                                <div id="proof-preview-box" class="text-center p-2 border rounded bg-white" style="{{ $transaction->payment_proof ? '' : 'display: none;' }}">
                                    <img id="proof-preview-img" src="{{ $transaction->payment_proof_url ?? '' }}" alt="Bukti QRIS" class="img-thumbnail mb-2" style="max-height: 140px; object-fit: contain;">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <span class="badge bg-success-subtle text-success small" id="proof-status-badge"><i class="bi bi-check2-circle me-1"></i>{{ $transaction->payment_proof ? 'Foto Tersimpan' : 'Foto Siap' }}</span>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 small" id="btn-delete-proof">
                                            <i class="bi bi-trash"></i> Hapus Foto
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    let currentTotal = 0;

    const formatRupiah = (val) => 'Rp ' + Number(val).toLocaleString('id-ID', { minimumFractionDigits: 0 });

    const cartInput = document.getElementById('cart-input');
    const cartBody = document.getElementById('cart-items-body');
    const emptyRow = document.getElementById('empty-cart-row');
    const totalItemsEl = document.getElementById('summary-total-items');
    const totalAmountEl = document.getElementById('summary-total-amount');
    const submitBtn = document.getElementById('btn-submit-order');
    const clearBtn = document.getElementById('btn-clear-cart');
    const menuSearch = document.getElementById('menu-search');

    const cashInput = document.getElementById('cash_input');
    const changeDisplay = document.getElementById('change-display');
    const cashPanel = document.getElementById('cash-panel');
    const qrisPanel = document.getElementById('qris-panel');

    function updateChange() {
        const payMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
        if (payMethod === 'cash') {
            const paid = parseInt(cashInput.value) || 0;
            const change = paid - currentTotal;
            if (paid > 0 && change >= 0) {
                changeDisplay.textContent = formatRupiah(change);
                changeDisplay.className = 'fw-bold text-success fs-6';
            } else if (paid > 0 && change < 0) {
                changeDisplay.textContent = 'Kurang ' + formatRupiah(Math.abs(change));
                changeDisplay.className = 'fw-bold text-danger fs-6';
            } else {
                changeDisplay.textContent = 'Rp 0';
                changeDisplay.className = 'fw-bold text-primary fs-6';
            }
        }
    }

    cashInput.addEventListener('input', updateChange);

    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'cash') {
                cashPanel.style.display = 'block';
                qrisPanel.style.display = 'none';
            } else {
                cashPanel.style.display = 'none';
                qrisPanel.style.display = 'block';
            }
            updateChange();
        });
    });

    document.querySelectorAll('.quick-cash-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const val = this.dataset.val;
            if (val === 'exact') {
                cashInput.value = currentTotal;
            } else {
                cashInput.value = val;
            }
            updateChange();
        });
    });

    function renderCart() {
        if (cart.length === 0) {
            cartBody.innerHTML = `
                <tr id="empty-cart-row">
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="bi bi-cart-x fs-1 d-block mb-2 opacity-50"></i>
                        Keranjang masih kosong.<br><small>Pilih menu di samping untuk menambahkan.</small>
                    </td>
                </tr>`;
            currentTotal = 0;
            totalItemsEl.textContent = '0';
            totalAmountEl.textContent = 'Rp 0';
            submitBtn.disabled = true;
            clearBtn.style.display = 'none';
            cartInput.value = '[]';
            updateChange();
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
        currentTotal = totalSum;
        totalItemsEl.textContent = totalQty;
        totalAmountEl.textContent = formatRupiah(totalSum);
        submitBtn.disabled = false;
        clearBtn.style.display = 'inline-block';
        cartInput.value = JSON.stringify(cart);
        updateChange();
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
            cashInput.value = '';
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

    // QRIS Camera & Upload Logic
    const cameraStreamBox = document.getElementById('camera-stream-box');
    const cameraVideo = document.getElementById('camera-video');
    const cameraCanvas = document.getElementById('camera-canvas');
    const btnOpenCamera = document.getElementById('btn-open-camera');
    const btnSnapPhoto = document.getElementById('btn-snap-photo');
    const btnStopCamera = document.getElementById('btn-stop-camera');
    const btnPickFile = document.getElementById('btn-pick-file');
    const fileInput = document.getElementById('payment_proof_file');
    const cameraInput = document.getElementById('payment_proof_camera');
    const removeProofInput = document.getElementById('remove_payment_proof');
    const cameraErrorMsg = document.getElementById('camera-error-msg');
    const previewBox = document.getElementById('proof-preview-box');
    const previewImg = document.getElementById('proof-preview-img');
    const proofBadge = document.getElementById('proof-status-badge');
    const btnDeleteProof = document.getElementById('btn-delete-proof');

    let mediaStream = null;

    function stopCameraStream() {
        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }
        if (cameraVideo) {
            cameraVideo.srcObject = null;
        }
        if (cameraStreamBox) {
            cameraStreamBox.style.display = 'none';
        }
    }

    if (btnOpenCamera) {
        btnOpenCamera.addEventListener('click', async function () {
            cameraErrorMsg.style.display = 'none';
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                cameraErrorMsg.textContent = 'Browser tidak mendukung akses kamera langsung. Silakan gunakan tombol "Unggah File".';
                cameraErrorMsg.style.display = 'block';
                return;
            }
            try {
                mediaStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: false
                });
                cameraVideo.srcObject = mediaStream;
                await cameraVideo.play();
                cameraStreamBox.style.display = 'block';
            } catch (err) {
                cameraErrorMsg.textContent = 'Tidak dapat mengakses kamera (' + (err.name || 'Akses ditolak') + '). Gunakan tombol "Unggah File".';
                cameraErrorMsg.style.display = 'block';
                stopCameraStream();
            }
        });
    }

    if (btnSnapPhoto) {
        btnSnapPhoto.addEventListener('click', function () {
            if (!cameraVideo.videoWidth) return;
            cameraCanvas.width = cameraVideo.videoWidth;
            cameraCanvas.height = cameraVideo.videoHeight;
            const ctx = cameraCanvas.getContext('2d');
            ctx.drawImage(cameraVideo, 0, 0, cameraCanvas.width, cameraCanvas.height);

            const dataUrl = cameraCanvas.toDataURL('image/jpeg', 0.85);
            cameraInput.value = dataUrl;
            if (fileInput) fileInput.value = '';
            if (removeProofInput) removeProofInput.value = '0';

            previewImg.src = dataUrl;
            if (proofBadge) proofBadge.innerHTML = '<i class="bi bi-check2-circle me-1"></i>Foto Baru Siap';
            previewBox.style.display = 'block';
            stopCameraStream();
        });
    }

    if (btnStopCamera) {
        btnStopCamera.addEventListener('click', function () {
            stopCameraStream();
        });
    }

    if (btnPickFile) {
        btnPickFile.addEventListener('click', function () {
            fileInput.click();
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    if (proofBadge) proofBadge.innerHTML = '<i class="bi bi-check2-circle me-1"></i>File Baru Siap';
                    previewBox.style.display = 'block';
                    cameraInput.value = '';
                    if (removeProofInput) removeProofInput.value = '0';
                    stopCameraStream();
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (btnDeleteProof) {
        btnDeleteProof.addEventListener('click', function () {
            cameraInput.value = '';
            if (fileInput) fileInput.value = '';
            previewImg.src = '';
            if (removeProofInput) removeProofInput.value = '1';
            previewBox.style.display = 'none';
            stopCameraStream();
        });
    }

    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value !== 'qris') {
                stopCameraStream();
            }
        });
    });

    // Submit order confirmation
    submitBtn.addEventListener('click', function () {
        if (cart.length === 0) return;
        const totalSum = totalAmountEl.textContent;
        const totalItems = totalItemsEl.textContent;
        const payMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';

        let methodLabel = payMethod === 'qris' ? 'QRIS' : 'Cash (Tunai)';

        if (payMethod === 'cash') {
            const paid = parseInt(cashInput.value) || 0;
            if (paid > 0 && paid < currentTotal) {
                alert('Nominal uang diterima kurang dari total pembayaran (' + totalSum + ')');
                cashInput.focus();
                return;
            }
        }

        if (window.Swal) {
            Swal.fire({
                title: 'Konfirmasi Update Transaksi',
                html: `
                    <div class="text-start p-2 bg-light rounded small">
                        <div class="mb-1">Item: <strong>${totalItems} item</strong></div>
                        <div class="mb-1">Total: <strong class="text-success fs-6">${totalSum}</strong></div>
                        <div class="mb-1">Metode: <span class="badge bg-primary">${methodLabel}</span></div>
                    </div>
                    <div class="mt-2">Simpan perubahan transaksi ini sekarang?</div>
                `,
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
