<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Menu;
use App\Models\OperationalCash;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display main dashboard view.
     */
    public function index(Request $request): View
    {
        // Detect DB driver for SQLite-safe expressions
        $driver = DB::getDriverName(); // 'sqlite' or 'mysql'

        // Date range filter (default 30 hari terakhir)
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::now()->subDays(29)->startOfDay();
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::now()->endOfDay();

        $user = $request->user();
        // Branch filter: if cashier has branch_id, lock to their branch
        if ($user && $user->isKasir() && $user->branch_id) {
            $branchId = $user->branch_id;
        } else {
            $branchId = $request->input('branch_id');
        }

        // ── Kartu Ringkasan ───────────────────────────────────────────────
        $baseQuery = Transaction::whereBetween('transaction_date', [
            $dateFrom->toDateString(),
            $dateTo->toDateString(),
        ]);

        if ($branchId) {
            $baseQuery->where('branch_id', $branchId);
        }

        $totalOmzet        = (clone $baseQuery)->sum('total_amount');
        $totalTransactions = (clone $baseQuery)->count();
        $totalItems        = (clone $baseQuery)->sum('total_items');
        $avgTransaction    = $totalTransactions > 0
            ? (int) round($totalOmzet / $totalTransactions)
            : 0;

        $omzetTodayQ = Transaction::whereDate('transaction_date', Carbon::today()->toDateString());
        $trxTodayQ   = Transaction::whereDate('transaction_date', Carbon::today()->toDateString());
        if ($branchId) {
            $omzetTodayQ->where('branch_id', $branchId);
            $trxTodayQ->where('branch_id', $branchId);
        }
        $omzetToday = $omzetTodayQ->sum('total_amount');
        $trxToday   = $trxTodayQ->count();

        // Payment Methods Breakdown (Cash vs QRIS)
        $paymentBreakdown = (clone $baseQuery)
            ->select('payment_method', DB::raw('SUM(total_amount) as omzet'), DB::raw('COUNT(*) as total'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $cashExpensePeriod = OperationalCash::where('date', '>=', $dateFrom->toDateString())
            ->where('date', '<=', $dateTo->toDateString() . ' 23:59:59')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->pengeluaran()
            ->sum('amount');

        $cashSavingsPeriod = OperationalCash::where('date', '>=', $dateFrom->toDateString())
            ->where('date', '<=', $dateTo->toDateString() . ' 23:59:59')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->tabungan()
            ->sum('amount');

        $stats = [
            'total_omzet'        => $totalOmzet,
            'total_transactions' => $totalTransactions,
            'total_items'        => $totalItems,
            'avg_transaction'    => $avgTransaction,
            'omzet_today'        => $omzetToday,
            'trx_today'          => $trxToday,
            'total_menu'         => Menu::count(),
            'total_category'     => Category::count(),
            'omzet_cash'         => (int) ($paymentBreakdown['cash']->omzet ?? 0),
            'omzet_qris'         => (int) ($paymentBreakdown['qris']->omzet ?? 0),
            'count_cash'         => (int) ($paymentBreakdown['cash']->total ?? 0),
            'count_qris'         => (int) ($paymentBreakdown['qris']->total ?? 0),
            'total_expense'      => (int) $cashExpensePeriod,
            'total_savings'      => (int) $cashSavingsPeriod,
        ];

        // ── Tren Omzet per Hari ───────────────────────────────────────────
        $trendDays = (clone $baseQuery)
            ->select(
                DB::raw('DATE(transaction_date) as tgl'),
                DB::raw('SUM(total_amount) as omzet'),
                DB::raw('COUNT(*) as jumlah')
            )
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get();

        // ── Omzet per Jam (hari ini) ──────────────────────────────────────
        $hourExpr = $driver === 'sqlite'
            ? "CAST(strftime('%H', transaction_time) AS INTEGER)"
            : 'HOUR(transaction_time)';

        $trendJamQ = Transaction::whereDate('transaction_date', Carbon::today()->toDateString());
        if ($branchId) {
            $trendJamQ->where('branch_id', $branchId);
        }

        $trendJam = $trendJamQ->select(
                DB::raw("$hourExpr as jam"),
                DB::raw('SUM(total_amount) as omzet'),
                DB::raw('COUNT(*) as jumlah')
            )
            ->groupBy('jam')
            ->orderBy('jam')
            ->get()
            ->map(fn($r) => [
                'jam'    => (int) $r->jam,
                'omzet'  => (int) $r->omzet,
                'jumlah' => (int) $r->jumlah,
            ]);

        // ── Omzet per Hari dalam Seminggu ─────────────────────────────────
        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $dowExpr  = $driver === 'sqlite'
            ? "CAST(strftime('%w', transaction_date) AS INTEGER)"
            : 'DAYOFWEEK(transaction_date)';

        $trendHari = (clone $baseQuery)
            ->select(
                DB::raw("$dowExpr as hari_num"),
                DB::raw('SUM(total_amount) as omzet'),
                DB::raw('COUNT(*) as jumlah')
            )
            ->groupBy('hari_num')
            ->orderBy('hari_num')
            ->get()
            ->map(function ($row) use ($dayNames, $driver) {
                $num = (int) $row->hari_num;
                if ($driver === 'sqlite') {
                    $idx = $num === 0 ? 6 : ($num - 1);
                } else {
                    $idx = $num === 1 ? 6 : ($num - 2);
                }
                return [
                    'hari'   => $dayNames[$idx] ?? 'Minggu',
                    'omzet'  => (int) $row->omzet,
                    'jumlah' => (int) $row->jumlah,
                ];
            });

        // ── Top 10 Menu Terlaris ──────────────────────────────────────────
        $topMenusQ = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('menus', 'transaction_items.menu_id', '=', 'menus.id')
            ->whereBetween('transactions.transaction_date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
            ]);

        if ($branchId) {
            $topMenusQ->where('transactions.branch_id', $branchId);
        }

        $topMenus = $topMenusQ->select(
                'menus.name as nama',
                DB::raw('SUM(transaction_items.qty) as qty'),
                DB::raw('SUM(transaction_items.subtotal) as omzet')
            )
            ->groupBy('menus.id', 'menus.name')
            ->orderByDesc('omzet')
            ->limit(10)
            ->get();

        // ── Omzet per Kategori ────────────────────────────────────────────
        $topKategoriQ = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('menus', 'transaction_items.menu_id', '=', 'menus.id')
            ->join('categories', 'menus.category_id', '=', 'categories.id')
            ->whereBetween('transactions.transaction_date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
            ]);

        if ($branchId) {
            $topKategoriQ->where('transactions.branch_id', $branchId);
        }

        $topKategori = $topKategoriQ->select(
                'categories.name as nama',
                DB::raw('SUM(transaction_items.subtotal) as omzet')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('omzet')
            ->get();

        // ── Pre-build chart-ready arrays (simple scalars, safe for @json) ─
        $chartTrendLabels = $trendDays->pluck('tgl')->values()->all();
        $chartTrendOmzet  = $trendDays->pluck('omzet')->map(fn($v) => (int) $v)->values()->all();
        $chartTrendJumlah = $trendDays->pluck('jumlah')->map(fn($v) => (int) $v)->values()->all();

        $chartJamLabels   = $trendJam->map(fn($r) => str_pad($r['jam'], 2, '0', STR_PAD_LEFT) . ':00')->values()->all();
        $chartJamOmzet    = $trendJam->pluck('omzet')->map(fn($v) => (int) $v)->values()->all();

        $chartHariLabels  = collect($trendHari)->pluck('hari')->values()->all();
        $chartHariOmzet   = collect($trendHari)->pluck('omzet')->map(fn($v) => (int) $v)->values()->all();

        $chartMenuLabels  = $topMenus->pluck('nama')->values()->all();
        $chartMenuOmzet   = $topMenus->pluck('omzet')->map(fn($v) => (int) $v)->values()->all();

        $chartKatLabels   = $topKategori->pluck('nama')->values()->all();
        $chartKatOmzet    = $topKategori->pluck('omzet')->map(fn($v) => (int) $v)->values()->all();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $selectedBranchId = $branchId;

        return view('dashboard.index', compact(
            'stats',
            'dateFrom',
            'dateTo',
            'trendDays',
            'trendJam',
            'trendHari',
            'topMenus',
            'topKategori',
            'branches',
            'selectedBranchId',
            'chartTrendLabels', 'chartTrendOmzet', 'chartTrendJumlah',
            'chartJamLabels',   'chartJamOmzet',
            'chartHariLabels',  'chartHariOmzet',
            'chartMenuLabels',  'chartMenuOmzet',
            'chartKatLabels',   'chartKatOmzet'
        ));
    }
}
