<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Show POS transaction input UI.
     */
    public function create(): View
    {
        // Only active menus are allowed for POS
        $menus = Menu::where('is_active', true)->orderBy('name')->get();
        return view('transactions.create', compact('menus'));
    }

    /**
     * Store a new transaction.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate cart payload (JSON string)
        $validated = $request->validate([
            'cart' => ['required', 'json'],
        ]);

        $cart = json_decode($validated['cart'], true);
        if (empty($cart) || !is_array($cart)) {
            return back()->with('error', 'Keranjang kosong.');
        }

        // Server‑side validation of each line
        foreach ($cart as $item) {
            if (!isset($item['menu_id'], $item['qty'])) {
                return back()->with('error', 'Data item tidak lengkap.');
            }
            if ($item['qty'] <= 0) {
                return back()->with('error', 'Kuantitas harus lebih dari 0.');
            }
        }

        // Begin atomic DB transaction
        DB::transaction(function () use ($cart, $request) {
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

            $totalItems = 0;
            $totalAmount = 0;
            $transaction = Transaction::create([
                'transaction_code' => $code,
                'transaction_date' => Carbon::now()->toDateString(),
                'transaction_time' => Carbon::now()->toTimeString(),
                'total_items' => 0, // will be updated later
                'total_amount' => 0,
                'created_by' => $request->user()->id,
            ]);

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
                    'menu_id' => $menu->id,
                    'qty' => $qty,
                    'price' => $price,
                    'discount' => $discount,
                    'subtotal' => $subtotal,
                ]);
                $totalItems += $qty;
                $totalAmount += $subtotal;
            }

            $transaction->update([
                'total_items' => $totalItems,
                'total_amount' => $totalAmount,
            ]);
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    /**
     * List transactions (accessible to all authenticated users).
     */
    public function index(Request $request): View
    {
        $query = Transaction::with('creator');

        // Filters
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

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Show transaction detail.
     */
    public function show(Transaction $transaction): View
    {
        $transaction->load('items.menu');
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show edit form (admin only).
     */
    public function edit(Transaction $transaction): View
    {
        Gate::authorize('admin');
        $transaction->load('items.menu');
        $menus = Menu::withTrashed()->orderBy('name')->get();
        $existingItems = $transaction->items->map(function ($i) {
            return [
                'menu_id' => $i->menu_id,
                'name' => $i->menu->name ?? 'Menu #' . $i->menu_id,
                'qty' => (int) $i->qty,
                'price' => (int) $i->price,
                'discount' => (int) $i->discount,
                'subtotal' => (int) $i->subtotal,
            ];
        })->values()->all();

        return view('transactions.edit', compact('transaction', 'menus', 'existingItems'));
    }

    /**
     * Update transaction (admin only).
     */
    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('admin');
        // For simplicity we reuse the same logic as store but replace items
        $validated = $request->validate([
            'cart' => ['required', 'json'],
        ]);
        $cart = json_decode($validated['cart'], true);

        DB::transaction(function () use ($cart, $transaction, $request) {
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
                    'menu_id' => $menu->id,
                    'qty' => $qty,
                    'price' => $price,
                    'discount' => $discount,
                    'subtotal' => $subtotal,
                ]);
                $totalItems += $qty;
                $totalAmount += $subtotal;
            }

            $transaction->update([
                'total_items' => $totalItems,
                'total_amount' => $totalAmount,
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
        $transaction->items()->delete();
        $transaction->delete();
        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }
}
