<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\OperationalCash;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationalCashController extends Controller
{
    /**
     * Tampilkan daftar transaksi kas harian & belanja warung.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::now()->startOfMonth();
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::now()->endOfDay();

        // Branch filtering: kasir terkunci ke cabangnya
        if ($user && $user->isKasir() && $user->branch_id) {
            $branchId = $user->branch_id;
        } else {
            $branchId = $request->input('branch_id');
        }

        $type = $request->input('type');
        $search = $request->input('search');

        $query = OperationalCash::with(['branch', 'user'])
            ->where('date', '>=', $dateFrom->toDateString())
            ->where('date', '<=', $dateTo->toDateString() . ' 23:59:59');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($type && in_array($type, ['pengeluaran', 'tabungan'])) {
            $query->where('type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Hitung total ringkasan
        $totalPengeluaran = (clone $query)->where('type', 'pengeluaran')->sum('amount');
        $totalTabungan    = (clone $query)->where('type', 'tabungan')->sum('amount');
        $countTotal       = (clone $query)->count();

        $records = $query->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('operational-cash.index', compact(
            'records',
            'branches',
            'dateFrom',
            'dateTo',
            'branchId',
            'type',
            'search',
            'totalPengeluaran',
            'totalTabungan',
            'countTotal'
        ));
    }

    /**
     * Tampilkan formulir pencatatan kas/belanja baru.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $userBranch = $user && $user->branch_id ? $user->branch : null;
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('operational-cash.create', compact('branches', 'userBranch'));
    }

    /**
     * Simpan catatan kas operasional / belanja harian.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date'      => ['required', 'date'],
            'time'      => ['nullable', 'date_format:H:i'],
            'type'      => ['required', 'string', 'in:pengeluaran,tabungan'],
            'category'  => ['nullable', 'string', 'max:50'],
            'name'      => ['required', 'string', 'max:255'],
            'amount'    => ['required', 'numeric', 'min:1'],
            'notes'     => ['nullable', 'string', 'max:500'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ], [
            'date.required'   => 'Tanggal wajib diisi.',
            'type.required'   => 'Jenis kas (Belanja / Tabungan) wajib dipilih.',
            'name.required'   => 'Nama keperluan / barang wajib diisi.',
            'amount.required' => 'Nominal pengeluaran/tabungan wajib diisi.',
            'amount.min'      => 'Nominal minimal Rp 1.',
        ]);

        $user = $request->user();
        $branchId = ($user && $user->isKasir() && $user->branch_id)
            ? $user->branch_id
            : ($validated['branch_id'] ?? $user?->branch_id);

        OperationalCash::create([
            'date'      => $validated['date'],
            'time'      => $validated['time'] ?? now()->format('H:i:s'),
            'branch_id' => $branchId,
            'user_id'   => $user->id,
            'type'      => $validated['type'],
            'category'  => $validated['category'] ?? null,
            'name'      => $validated['name'],
            'amount'    => (int) $validated['amount'],
            'notes'     => $validated['notes'] ?? null,
        ]);

        $label = $validated['type'] === 'tabungan' ? 'Tabungan harian' : 'Belanja operasional';
        return redirect()->route('operational-cash.index')
            ->with('success', "{$label} berhasil dicatat!");
    }

    /**
     * Tampilkan form edit catatan kas.
     */
    public function edit(Request $request, OperationalCash $operationalCash): View
    {
        $user = $request->user();
        if ($user->isKasir() && $operationalCash->branch_id && $operationalCash->branch_id !== $user->branch_id) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah catatan cabang lain.');
        }

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('operational-cash.edit', compact('operationalCash', 'branches'));
    }

    /**
     * Update catatan kas.
     */
    public function update(Request $request, OperationalCash $operationalCash): RedirectResponse
    {
        $user = $request->user();
        if ($user->isKasir() && $operationalCash->branch_id && $operationalCash->branch_id !== $user->branch_id) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah catatan cabang lain.');
        }

        $validated = $request->validate([
            'date'      => ['required', 'date'],
            'time'      => ['nullable', 'date_format:H:i'],
            'type'      => ['required', 'string', 'in:pengeluaran,tabungan'],
            'category'  => ['nullable', 'string', 'max:50'],
            'name'      => ['required', 'string', 'max:255'],
            'amount'    => ['required', 'numeric', 'min:1'],
            'notes'     => ['nullable', 'string', 'max:500'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $branchId = ($user && $user->isKasir() && $user->branch_id)
            ? $user->branch_id
            : ($validated['branch_id'] ?? $operationalCash->branch_id);

        $operationalCash->update([
            'date'      => $validated['date'],
            'time'      => $validated['time'] ?? $operationalCash->time,
            'branch_id' => $branchId,
            'type'      => $validated['type'],
            'category'  => $validated['category'] ?? null,
            'name'      => $validated['name'],
            'amount'    => (int) $validated['amount'],
            'notes'     => $validated['notes'] ?? null,
        ]);

        return redirect()->route('operational-cash.index')
            ->with('success', 'Catatan kas berhasil diperbarui!');
    }

    /**
     * Hapus catatan kas.
     */
    public function destroy(Request $request, OperationalCash $operationalCash): RedirectResponse
    {
        $user = $request->user();
        if ($user->isKasir() && $operationalCash->branch_id && $operationalCash->branch_id !== $user->branch_id) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus catatan cabang lain.');
        }

        $name = $operationalCash->name;
        $operationalCash->delete();

        return redirect()->route('operational-cash.index')
            ->with('success', "Catatan \"{$name}\" berhasil dihapus!");
    }
}
