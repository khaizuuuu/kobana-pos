<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display the report page with filters and summary tables.
     */
    public function index(Request $request): View
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::now()->startOfMonth();
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::now()->endOfDay();

        $categoryId    = $request->input('category_id');
        $branchId      = $request->input('branch_id');
        $paymentMethod = $request->input('payment_method');

        $categories = Category::orderBy('name')->get();
        $branches   = Branch::where('is_active', true)->orderBy('name')->get();

        // ── Ringkasan Periode ─────────────────────────────────────────────
        $baseQ = Transaction::whereBetween('transaction_date', [
            $dateFrom->toDateString(),
            $dateTo->toDateString(),
        ]);

        if ($branchId) {
            $baseQ->where('branch_id', $branchId);
        }
        if ($paymentMethod) {
            $baseQ->where('payment_method', $paymentMethod);
        }

        $totalOmzet = (clone $baseQ)->sum('total_amount');
        $totalTrx   = (clone $baseQ)->count();
        $totalItem  = (clone $baseQ)->sum('total_items');
        $avgTrx     = $totalTrx > 0 ? (int) round($totalOmzet / $totalTrx) : 0;

        // ── Laporan Harian ────────────────────────────────────────────────
        $dayNamesId = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $daily = (clone $baseQ)
            ->select(
                DB::raw('DATE(transaction_date) as tgl'),
                DB::raw('COUNT(*) as jumlah'),
                DB::raw('SUM(total_items) as item'),
                DB::raw('SUM(total_amount) as omzet')
            )
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get()
            ->map(function ($r) use ($dayNamesId) {
                $carbon  = Carbon::parse($r->tgl);
                $r->hari = $dayNamesId[$carbon->dayOfWeek] ?? '-';
                return $r;
            });

        // ── Laporan per Menu ──────────────────────────────────────────────
        $menuQ = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('menus', 'transaction_items.menu_id', '=', 'menus.id')
            ->join('categories', 'menus.category_id', '=', 'categories.id')
            ->whereBetween('transactions.transaction_date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
            ]);

        if ($branchId) {
            $menuQ->where('transactions.branch_id', $branchId);
        }
        if ($paymentMethod) {
            $menuQ->where('transactions.payment_method', $paymentMethod);
        }
        if ($categoryId) {
            $menuQ->where('categories.id', $categoryId);
        }

        $menuReport = $menuQ
            ->select(
                'categories.name as kategori',
                'menus.code as kode',
                'menus.name as nama',
                DB::raw('SUM(transaction_items.qty) as qty'),
                DB::raw('SUM(transaction_items.subtotal) as omzet')
            )
            ->groupBy('categories.id', 'categories.name', 'menus.id', 'menus.code', 'menus.name')
            ->orderBy('categories.name')
            ->orderByDesc('omzet')
            ->get();

        return view('reports.index', compact(
            'dateFrom', 'dateTo', 'categories', 'categoryId', 'branches', 'branchId', 'paymentMethod',
            'totalOmzet', 'totalTrx', 'totalItem', 'avgTrx',
            'daily', 'menuReport'
        ));
    }

    /**
     * Export to CSV (no dependency needed – works out of the box).
     */
    public function exportExcel(Request $request): Response
    {
        $dateFrom      = $request->filled('date_from') ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth();
        $dateTo        = $request->filled('date_to')   ? Carbon::parse($request->date_to)   : Carbon::now();
        $categoryId    = $request->input('category_id');
        $branchId      = $request->input('branch_id');
        $paymentMethod = $request->input('payment_method');

        $query = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->leftJoin('branches', 'transactions.branch_id', '=', 'branches.id')
            ->join('menus', 'transaction_items.menu_id', '=', 'menus.id')
            ->join('categories', 'menus.category_id', '=', 'categories.id')
            ->whereBetween('transactions.transaction_date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
            ]);

        if ($branchId) {
            $query->where('transactions.branch_id', $branchId);
        }
        if ($paymentMethod) {
            $query->where('transactions.payment_method', $paymentMethod);
        }
        if ($categoryId) {
            $query->where('categories.id', $categoryId);
        }

        $rows = $query
            ->select(
                'transactions.transaction_code as kode_trx',
                'transactions.transaction_date as tanggal',
                'transactions.transaction_time as jam',
                'transactions.payment_method as metode_bayar',
                'branches.name as nama_cabang',
                'categories.name as kategori',
                'menus.code as kode_menu',
                'menus.name as nama_menu',
                'transaction_items.qty as qty',
                'transaction_items.price as harga',
                'transaction_items.discount as diskon',
                'transaction_items.subtotal as subtotal'
            )
            ->orderBy('transactions.transaction_date')
            ->orderBy('transactions.id')
            ->get();

        // Build CSV
        $csv  = "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        $csv .= "Kode Transaksi,Tanggal,Jam,Cabang,Metode Bayar,Kategori,Kode Menu,Nama Menu,Qty,Harga,Diskon,Subtotal\n";
        foreach ($rows as $r) {
            $csv .= implode(',', [
                $r->kode_trx,
                $r->tanggal,
                $r->jam,
                '"' . str_replace('"', '""', $r->nama_cabang ?? 'Pusat') . '"',
                strtoupper($r->metode_bayar ?? 'CASH'),
                '"' . str_replace('"', '""', $r->kategori) . '"',
                $r->kode_menu,
                '"' . str_replace('"', '""', $r->nama_menu) . '"',
                $r->qty,
                $r->harga,
                $r->diskon,
                $r->subtotal,
            ]) . "\n";
        }

        $filename = 'laporan-penjualan-' . $dateFrom->format('Ymd') . '-' . $dateTo->format('Ymd') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export to PDF using a simple Blade-to-HTML print approach.
     */
    public function exportPdf(Request $request): Response
    {
        $dateFrom      = $request->filled('date_from') ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth();
        $dateTo        = $request->filled('date_to')   ? Carbon::parse($request->date_to)   : Carbon::now();
        $categoryId    = $request->input('category_id');
        $branchId      = $request->input('branch_id');
        $paymentMethod = $request->input('payment_method');

        $baseQ = Transaction::whereBetween('transaction_date', [
            $dateFrom->toDateString(),
            $dateTo->toDateString(),
        ]);
        if ($branchId) {
            $baseQ->where('branch_id', $branchId);
        }
        if ($paymentMethod) {
            $baseQ->where('payment_method', $paymentMethod);
        }

        $totalOmzet = (clone $baseQ)->sum('total_amount');
        $totalTrx   = (clone $baseQ)->count();
        $totalItem  = (clone $baseQ)->sum('total_items');
        $avgTrx     = $totalTrx > 0 ? (int) round($totalOmzet / $totalTrx) : 0;

        $menuQ = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('menus', 'transaction_items.menu_id', '=', 'menus.id')
            ->join('categories', 'menus.category_id', '=', 'categories.id')
            ->whereBetween('transactions.transaction_date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
            ]);

        if ($branchId) {
            $menuQ->where('transactions.branch_id', $branchId);
        }
        if ($paymentMethod) {
            $menuQ->where('transactions.payment_method', $paymentMethod);
        }
        if ($categoryId) {
            $menuQ->where('categories.id', $categoryId);
        }

        $menuReport = $menuQ
            ->select(
                'categories.name as kategori',
                'menus.code as kode',
                'menus.name as nama',
                DB::raw('SUM(transaction_items.qty) as qty'),
                DB::raw('SUM(transaction_items.subtotal) as omzet')
            )
            ->groupBy('categories.id', 'categories.name', 'menus.id', 'menus.code', 'menus.name')
            ->orderBy('categories.name')
            ->orderByDesc('omzet')
            ->get();

        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Semua Cabang') : 'Semua Cabang';

        $html = view('reports.pdf', compact(
            'dateFrom', 'dateTo',
            'totalOmzet', 'totalTrx', 'totalItem', 'avgTrx',
            'menuReport', 'branchName', 'paymentMethod'
        ))->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}
