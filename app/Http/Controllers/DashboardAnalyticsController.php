<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Menu;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardAnalyticsController extends Controller
{
    /**
     * Return JSON data for dashboard charts.
     */
    public function index(): JsonResponse
    {
        // Total omzet per day (last 30 days)
        $trend = Transaction::select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(total_amount) as omzet')
            )
            ->where('transaction_date', '>=', Carbon::now()->subDays(30)->toDateString())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($row) => ['date' => $row->date, 'omzet' => (int) $row->omzet])
            ->values();

        // Omzet per hour for today
        $perHour = Transaction::select(
                DB::raw('HOUR(transaction_time) as hour'),
                DB::raw('SUM(total_amount) as omzet')
            )
            ->whereDate('transaction_date', Carbon::today()->toDateString())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($row) => ['hour' => $row->hour, 'omzet' => (int) $row->omzet])
            ->values();

        // Top 5 menus by total sold amount
        $topMenus = DB::table('transaction_items')
            ->join('menus', 'transaction_items.menu_id', '=', 'menus.id')
            ->select('menus.name as menu', DB::raw('SUM(transaction_items.subtotal) as omzet'))
            ->groupBy('menus.id')
            ->orderByDesc('omzet')
            ->limit(5)
            ->get()
            ->map(fn($row) => ['menu' => $row->menu, 'omzet' => (int) $row->omzet])
            ->values();

        // Top 5 categories by total sold amount
        $topCategories = DB::table('transaction_items')
            ->join('menus', 'transaction_items.menu_id', '=', 'menus.id')
            ->join('categories', 'menus.category_id', '=', 'categories.id')
            ->select('categories.name as category', DB::raw('SUM(transaction_items.subtotal) as omzet'))
            ->groupBy('categories.id')
            ->orderByDesc('omzet')
            ->limit(5)
            ->get()
            ->map(fn($row) => ['category' => $row->category, 'omzet' => (int) $row->omzet])
            ->values();

        return response()->json([
            'trend' => $trend,
            'perHour' => $perHour,
            'topMenus' => $topMenus,
            'topCategories' => $topCategories,
        ]);
    }
}
