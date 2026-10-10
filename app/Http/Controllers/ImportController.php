<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDatasetImportJob;
use App\Models\Category;
use App\Models\ImportLog;
use App\Models\Menu;
use App\Models\Transaction;
use App\Services\DatasetImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportController extends Controller
{
    /**
     * Display import page and history logs.
     */
    public function index(): View
    {
        $stats = [
            'menus_count' => Menu::count(),
            'categories_count' => Category::count(),
            'transactions_count' => Transaction::count(),
            'total_amount' => Transaction::sum('total_amount'),
        ];

        $logs = ImportLog::with('creator')->orderBy('created_at', 'desc')->paginate(10);

        return view('imports.index', compact('stats', 'logs'));
    }

    /**
     * Handle dataset upload.
     */
    public function store(Request $request, DatasetImportService $importService): RedirectResponse
    {
        $request->validate([
            'dataset_file' => 'required|file|mimes:xlsx,xls|max:51200', // max 50MB
        ], [
            'dataset_file.required' => 'Silakan pilih file Excel (.xlsx) terlebih dahulu.',
            'dataset_file.mimes' => 'Format file harus berupa file Excel berekstensi .xlsx atau .xls.',
            'dataset_file.max' => 'Ukuran file maksimal adalah 50MB.',
        ]);

        $file = $request->file('dataset_file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $targetDir = storage_path('app/imports');

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        $file->move($targetDir, $fileName);

        // Process directly or via queue
        try {
            // For instant feedback when uploaded directly via web
            $result = $importService->import($filePath, auth()->id());

            return redirect()->route('imports.index')->with(
                'success',
                "Impor dataset berhasil! Terimpor {$result['menus_count']} menu, {$result['transactions_count']} transaksi dengan total omzet " . format_rupiah($result['total_amount']) . "."
            );
        } catch (\Throwable $e) {
            return redirect()->route('imports.index')->with(
                'error',
                "Gagal mengimpor file: " . $e->getMessage()
            );
        }
    }
}
