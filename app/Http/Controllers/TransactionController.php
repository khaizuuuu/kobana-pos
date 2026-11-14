<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Show POS transaction input UI.
     */
    public function create(Request $request): View
    {
        // Only active menus are allowed for POS
        $menus = Menu::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $user = $request->user();
        $userBranch = $user?->branch;

        return view('transactions.create', compact('menus', 'branches', 'userBranch'));
    }

    /**
     * Store a new transaction.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate cart payload and payment info
        $validated = $request->validate([
            'cart'                 => ['required', 'json'],
            'branch_id'            => ['nullable', 'exists:branches,id'],
            'payment_method'       => ['required', 'string', 'in:cash,qris'],
            'cash_amount'          => ['nullable', 'numeric', 'min:0'],
            'payment_note'         => ['nullable', 'string', 'max:255'],
            'payment_proof_file'   => ['nullable', 'image', 'max:5120'],
            'payment_proof_camera' => ['nullable', 'string'],
        ]);

        $cart = json_decode($validated['cart'], true);
        if (empty($cart) || !is_array($cart)) {
            return back()->with('error', 'Keranjang belanja kosong.');
        }

        // Server-side validation of each line
        foreach ($cart as $item) {
            if (!isset($item['menu_id'], $item['qty'])) {
                return back()->with('error', 'Data item tidak lengkap.');
            }
            if ($item['qty'] <= 0) {
                return back()->with('error', 'Kuantitas harus lebih dari 0.');
            }
        }

        // Determine branch: cashier is locked to their branch, admin can choose
        $user = $request->user();
        if ($user && $user->isKasir() && $user->branch_id) {
            $branchId = $user->branch_id;
        } else {
            $branchId = $request->input('branch_id') ?: ($user?->branch_id ?: Branch::first()?->id);
        }

        $paymentMethod = $validated['payment_method'];
        $inputCash = (int) $request->input('cash_amount', 0);
        $paymentNote = $request->input('payment_note');
        $paymentProof = $this->handlePaymentProof($request);

        $transaction = null;

        // Begin atomic DB transaction
        DB::transaction(function () use ($cart, $request, $branchId, $paymentMethod, $inputCash, $paymentNote, $paymentProof, &$transaction) {
            // Generate unique transaction code atomically
            $today = Carbon::now()->format('Y-m-d');
            $seqRow = DB::table('transaction_sequences')
                ->where('date', $today)
                ->lockForUpdate()
                ->first();
            if ($seqRow) {
                $next = $seqRow->next_seq + 1;
                DB::table('transaction_sequences')
                    ->where('date', $today)
                    ->update(['next_seq' => $next]);
            } else {
                $next = 1;
                DB::table('transaction_sequences')->insert([
                    'date' => $today,
                    'next_seq' => $next,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $code = 'TRX' . Carbon::now()->format('Ymd') . sprintf('%05d', $next);

            $transaction = Transaction::create([
                'transaction_code' => $code,
                'transaction_date' => Carbon::now()->toDateString(),
                'transaction_time' => Carbon::now()->toTimeString(),
                'total_items'      => 0,
                'total_amount'     => 0,
                'branch_id'        => $branchId,
                'payment_method'   => $paymentMethod,
                'cash_amount'      => 0,
                'change_amount'    => 0,
                'payment_note'     => $paymentNote,
                'payment_proof'    => $paymentProof,
                'created_by'       => $request->user()->id,
            ]);

            $totalItems = 0;
            $totalAmount = 0;

            foreach ($cart as $item) {
                $menu = Menu::where('id', $item['menu_id'])->where('is_active', true)->firstOrFail();
                $qty = (int) $item['qty'];
                $price = $menu->price; // enforce server price
                $discount = isset($item['discount']) ? (int) $item['discount'] : 0;
                $subtotal = ($price * $qty) - $discount;
                if ($discount > $price * $qty) {
                    $discount = $price * $qty; // cap discount
                    $subtotal = 0;
                }
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'menu_id'        => $menu->id,
                    'qty'            => $qty,
                    'price'          => $price,
                    'discount'       => $discount,
                    'subtotal'       => $subtotal,
                ]);
                $totalItems += $qty;
                $totalAmount += $subtotal;
            }

            // Calculate cash received and change amount
            if ($paymentMethod === 'cash') {
                $cashAmount = $inputCash > 0 ? $inputCash : $totalAmount;
                $changeAmount = max(0, $cashAmount - $totalAmount);
            } else {
                $cashAmount = $totalAmount;
                $changeAmount = 0;
            }

            $transaction->update([
                'total_items'   => $totalItems,
                'total_amount'  => $totalAmount,
                'cash_amount'   => $cashAmount,
                'change_amount' => $changeAmount,
            ]);
        });

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaksi ' . $transaction->transaction_code . ' berhasil disimpan!');
    }

    /**
     * List transactions (accessible to all authenticated users).
     */
    public function index(Request $request): View
    {
        $query = Transaction::with(['creator', 'branch']);
        $user = $request->user();

        // Branch filtering: if cashier has an assigned branch, lock to their branch
        if ($user && $user->isKasir() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        // Payment Method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        // Date & Search Filters
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->input('date_to'));
        }
        if ($request->filled('code')) {
            $query->where('transaction_code', 'like', "%{$request->input('code')}%");
        }
        if ($request->filled('menu')) {
            $query->whereHas('items.menu', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->input('menu')}%");
            });
        }

        $transactions = $query->orderByDesc('transaction_date')
            ->orderByDesc('transaction_time')
            ->paginate(12)
            ->withQueryString();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'branches'));
    }

    /**
     * Show transaction detail.
     */
    public function show(Transaction $transaction): View
    {
        $transaction->load(['items.menu', 'branch', 'creator']);
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show edit form (admin only).
     */
    public function edit(Transaction $transaction): View
    {
        Gate::authorize('admin');
        $transaction->load(['items.menu', 'branch']);
        $menus = Menu::withTrashed()->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $existingItems = $transaction->items->map(function ($i) {
            return [
                'menu_id'  => $i->menu_id,
                'name'     => $i->menu->name ?? 'Menu #' . $i->menu_id,
                'qty'      => (int) $i->qty,
                'price'    => (int) $i->price,
                'discount' => (int) $i->discount,
                'subtotal' => (int) $i->subtotal,
            ];
        })->values()->all();

        return view('transactions.edit', compact('transaction', 'menus', 'branches', 'existingItems'));
    }

    /**
     * Update transaction (admin only).
     */
    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'cart'                 => ['required', 'json'],
            'branch_id'            => ['nullable', 'exists:branches,id'],
            'payment_method'       => ['required', 'string', 'in:cash,qris'],
            'cash_amount'          => ['nullable', 'numeric', 'min:0'],
            'payment_note'         => ['nullable', 'string', 'max:255'],
            'payment_proof_file'   => ['nullable', 'image', 'max:5120'],
            'payment_proof_camera' => ['nullable', 'string'],
            'remove_payment_proof' => ['nullable', 'boolean'],
        ]);

        $cart = json_decode($validated['cart'], true);
        if (empty($cart) || !is_array($cart)) {
            return back()->with('error', 'Keranjang belanja kosong.');
        }

        $paymentMethod = $validated['payment_method'];
        $inputCash = (int) $request->input('cash_amount', 0);
        $paymentNote = $request->input('payment_note');
        $branchId = $request->input('branch_id') ?: $transaction->branch_id;
        $paymentProof = $this->handlePaymentProof($request, $transaction->payment_proof);

        DB::transaction(function () use ($cart, $transaction, $branchId, $paymentMethod, $inputCash, $paymentNote, $paymentProof) {
            // Delete existing items
            $transaction->items()->delete();

            $totalItems = 0;
            $totalAmount = 0;

            foreach ($cart as $item) {
                $menu = Menu::where('id', $item['menu_id'])->where('is_active', true)->firstOrFail();
                $qty = (int) $item['qty'];
                $price = $menu->price;
                $discount = isset($item['discount']) ? (int) $item['discount'] : 0;
                $subtotal = ($price * $qty) - $discount;
                if ($discount > $price * $qty) {
                    $discount = $price * $qty;
                    $subtotal = 0;
                }
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'menu_id'        => $menu->id,
                    'qty'            => $qty,
                    'price'          => $price,
                    'discount'       => $discount,
                    'subtotal'       => $subtotal,
                ]);
                $totalItems += $qty;
                $totalAmount += $subtotal;
            }

            if ($paymentMethod === 'cash') {
                $cashAmount = $inputCash > 0 ? $inputCash : $totalAmount;
                $changeAmount = max(0, $cashAmount - $totalAmount);
            } else {
                $cashAmount = $totalAmount;
                $changeAmount = 0;
            }

            $transaction->update([
                'total_items'    => $totalItems,
                'total_amount'   => $totalAmount,
                'branch_id'      => $branchId,
                'payment_method' => $paymentMethod,
                'cash_amount'    => $cashAmount,
                'change_amount'  => $changeAmount,
                'payment_note'   => $paymentNote,
                'payment_proof'  => $paymentProof,
            ]);
        });

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    /**
     * Delete transaction (admin only).
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        Gate::authorize('admin');
        if ($transaction->payment_proof && Storage::disk('public')->exists($transaction->payment_proof)) {
            Storage::disk('public')->delete($transaction->payment_proof);
        }
        $transaction->items()->delete();
        $transaction->delete();
        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * Handle payment proof upload from file or base64 webcam/camera capture.
     */
    protected function handlePaymentProof(Request $request, ?string $currentProof = null): ?string
    {
        if ($request->boolean('remove_payment_proof')) {
            if ($currentProof && Storage::disk('public')->exists($currentProof)) {
                Storage::disk('public')->delete($currentProof);
            }
            $currentProof = null;
        }

        if ($request->hasFile('payment_proof_file')) {
            if ($currentProof && Storage::disk('public')->exists($currentProof)) {
                Storage::disk('public')->delete($currentProof);
            }
            return $request->file('payment_proof_file')->store('payment_proofs', 'public');
        }

        $cameraData = $request->input('payment_proof_camera');
        if ($cameraData && is_string($cameraData) && str_starts_with($cameraData, 'data:image/')) {
            if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $cameraData, $matches)) {
                $ext = strtolower($matches[1]) === 'png' ? 'png' : 'jpg';
                $decoded = base64_decode($matches[2]);
                if ($decoded !== false) {
                    if ($currentProof && Storage::disk('public')->exists($currentProof)) {
                        Storage::disk('public')->delete($currentProof);
                    }
                    $path = 'payment_proofs/qris_' . Str::random(20) . '.' . $ext;
                    Storage::disk('public')->put($path, $decoded);
                    return $path;
                }
            }
        }

        return $currentProof;
    }
}
