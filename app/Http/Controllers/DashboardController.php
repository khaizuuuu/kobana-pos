<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

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

        // ── Kartu Ringkasan ───────────────────────────────────────────────
        $baseQuery = Transaction::whereBetween('transaction_date', [
            $dateFrom->toDateString(),
            $dateTo->toDateString(),
        ]);

        $totalOmzet        = (clone $baseQuery)->sum('total_amount');
        $totalTransactions = (clone $baseQuery)->count();
        $totalItems        = (clone $baseQuery)->sum('total_items');
        $avgTransaction    = $totalTransactions > 0
            ? (int) round($totalOmzet / $totalTransactions)
            : 0;

        $omzetToday = Transaction::whereDate('transaction_date', Carbon::today()->toDateString())
            ->sum('total_amount');
        $trxToday = Transaction::whereDate('transaction_date', Carbon::today()->toDateString())
            ->count();

        $stats = [
            'total_omzet'        => $totalOmzet,
            'total_transactions' => $totalTransactions,
            'total_items'        => $totalItems,
            'avg_transaction'    => $avgTransaction,
            'omzet_today'        => $omzetToday,
            'trx_today'          => $trxToday,
            'total_menu'         => Menu::count(),
            'total_category'     => Category::count(),
        ];

        // ── Tren Omzet per Hari ───────────────────────────────────────────
        // DATE() works in both SQLite and MySQL
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
        // SQLite: CAST(strftime('%H', time) AS INTEGER)
        // MySQL : HOUR(time)
        $hourExpr = $driver === 'sqlite'
            ? "CAST(strftime('%H', transaction_time) AS INTEGER)"
            : 'HOUR(transaction_time)';

        $trendJam = Transaction::whereDate('transaction_date', Carbon::today()->toDateString())
            ->select(
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
        // SQLite: strftime('%w', date) → 0=Sun, 1=Mon … 6=Sat
        // MySQL : DAYOFWEEK(date)      → 1=Sun, 2=Mon … 7=Sat
        $dayNames  = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $dowExpr   = $driver === 'sqlite'
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
                // SQLite  : 0=Minggu, 1=Senin … 6=Sabtu
                // MySQL   : 1=Minggu, 2=Senin … 7=Sabtu
                $num = (int) $row->hari_num;
                if ($driver === 'sqlite') {
                    // 0→6(Minggu), 1→0(Senin) … 6→5(Sabtu)
                    $idx = $num === 0 ? 6 : ($num - 1);
                } else {
                    // 1→6(Minggu), 2→0(Senin) … 7→5(Sabtu)
                    $idx = $num === 1 ? 6 : ($num - 2);
                }
                return [
                    'hari'   => $dayNames[$idx] ?? 'Minggu',
                    'omzet'  => (int) $row->omzet,
                    'jumlah' => (int) $row->jumlah,
                ];
            });

        // ── Top 10 Menu Terlaris ──────────────────────────────────────────
        $topMenus = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('menus', 'transaction_items.menu_id', '=', 'menus.id')
            ->whereBetween('transactions.transaction_date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
            ])
            ->select(
                'menus.name as nama',
                DB::raw('SUM(transaction_items.qty) as qty'),
                DB::raw('SUM(transaction_items.subtotal) as omzet')
            )
            ->groupBy('menus.id', 'menus.name')
            ->orderByDesc('omzet')
            ->limit(10)
            ->get();

        // ── Omzet per Kategori ────────────────────────────────────────────
        $topKategori = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('menus', 'transaction_items.menu_id', '=', 'menus.id')
            ->join('categories', 'menus.category_id', '=', 'categories.id')
            ->whereBetween('transactions.transaction_date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
            ])
            ->select(
                'categories.name as nama',
                DB::raw('SUM(transaction_items.subtotal) as omzet')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('omzet')
            ->get();

        // ── Pre-build chart-ready arrays (simple scalars, safe for @json) ─
        $chartTrendLabels  = $trendDays->pluck('tgl')->values()->all();
        $chartTrendOmzet   = $trendDays->pluck('omzet')->map(fn($v) => (int) $v)->values()->all();
        $chartTrendJumlah  = $trendDays->pluck('jumlah')->map(fn($v) => (int) $v)->values()->all();

        $chartJamLabels    = $trendJam->map(fn($r) => str_pad($r['jam'], 2, '0', STR_PAD_LEFT) . ':00')->values()->all();
        $chartJamOmzet     = $trendJam->pluck('omzet')->map(fn($v) => (int) $v)->values()->all();

        $chartHariLabels   = collect($trendHari)->pluck('hari')->values()->all();
        $chartHariOmzet    = collect($trendHari)->pluck('omzet')->map(fn($v) => (int) $v)->values()->all();

        $chartMenuLabels   = $topMenus->pluck('nama')->values()->all();
        $chartMenuOmzet    = $topMenus->pluck('omzet')->map(fn($v) => (int) $v)->values()->all();

        $chartKatLabels    = $topKategori->pluck('nama')->values()->all();
        $chartKatOmzet     = $topKategori->pluck('omzet')->map(fn($v) => (int) $v)->values()->all();

        return view('dashboard.index', compact(
            'stats',
            'dateFrom',
            'dateTo',
            'trendDays',
            'trendJam',
            'trendHari',
            'topMenus',
            'topKategori',
            'chartTrendLabels', 'chartTrendOmzet', 'chartTrendJumlah',
            'chartJamLabels',   'chartJamOmzet',
            'chartHariLabels',  'chartHariOmzet',
            'chartMenuLabels',  'chartMenuOmzet',
            'chartKatLabels',   'chartKatOmzet'
        ));
    }
}
