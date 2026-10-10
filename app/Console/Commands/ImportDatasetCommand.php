<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DatasetImportService;
use Illuminate\Console\Command;

class ImportDatasetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kobana:import {path=database/data/kobana_dataset.xlsx}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dataset Kobana Fried Chicken dari file Excel (.xlsx)';

    /**
     * Execute the console command.
     */
    public function handle(DatasetImportService $importService): int
    {
        $pathArg = $this->argument('path');
        $fullPath = base_path($pathArg);

        if (!file_exists($fullPath)) {
            // Check if user placed it in workspace root or download
            if (file_exists(base_path('kobana_dataset.xlsx'))) {
                $fullPath = base_path('kobana_dataset.xlsx');
            } else {
                $this->error("File dataset tidak ditemukan pada path: {$fullPath}");
                $this->info("Silakan letakkan file dataset di: {$pathArg} atau di root proyek.");
                return self::FAILURE;
            }
        }

        $this->info("Memulai proses impor dari: {$fullPath}...");
        $startTime = microtime(true);

        try {
            $adminUser = User::where('role', 'admin')->first();
            $result = $importService->import($fullPath, $adminUser?->id);

            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info("✅ Impor dataset berhasil diselesaikan dalam {$duration} detik!");
            $this->table(
                ['Entitas', 'Jumlah / Nilai'],
                [
                    ['Kategori', $result['categories_count']],
                    ['Master Menu', $result['menus_count']],
                    ['Transaksi', number_format($result['transactions_count'], 0, ',', '.')],
                    ['Item Transaksi', number_format($result['items_count'], 0, ',', '.')],
                    ['Total Omzet', format_rupiah($result['total_amount'])],
                ]
            );

            if (!empty($result['warnings'])) {
                $this->warn('Catatan / Peringatan Rekonsiliasi:');
                foreach ($result['warnings'] as $warning) {
                    $this->line(" - {$warning}");
                }
            } else {
                $this->info("Rekonsiliasi 100% valid (Header cocok dengan Detail transaksi).");
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Gagal melakukan impor: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
