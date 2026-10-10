<?php

namespace App\Services;

use App\Models\Category;
use App\Models\ImportLog;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class DatasetImportService
{
    /**
     * Import dataset from an Excel file (.xlsx).
     *
     * @param string $filePath
     * @param int|null $userId
     * @return array
     */
    public function import(string $filePath, ?int $userId = null): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File dataset tidak ditemukan di: {$filePath}");
        }

        $importLog = ImportLog::create([
            'file_name' => basename($filePath),
            'status' => 'running',
            'created_by' => $userId,
            'created_at' => now(),
        ]);

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheetNames = $spreadsheet->getSheetNames();

            // 1. Sheet Master_Menu
            $masterSheet = $this->getSheetByName($spreadsheet, ['Master_Menu', 'Master Menu', 'Menu', 'master_menu']);
            if (!$masterSheet) {
                throw new \RuntimeException("Sheet Master_Menu tidak ditemukan dalam file Excel.");
            }

            $menuRows = $masterSheet->toArray(null, true, true, true);
            $menuMap = []; // code => Menu model
            $categoryMap = []; // name => Category model
            $categoryCount = 0;
            $menuCount = 0;

            DB::beginTransaction();

            $header = array_shift($menuRows); // remove header
            // Determine column indexes
            $codeCol = 'A';
            $nameCol = 'B';
            $catCol = 'C';
            $priceCol = 'D';

            foreach ($menuRows as $row) {
                $code = trim((string) ($row[$codeCol] ?? ''));
                $name = trim((string) ($row[$nameCol] ?? ''));
                $categoryName = trim((string) ($row[$catCol] ?? ''));
                $priceRaw = $row[$priceCol] ?? 0;
                $price = (int) preg_replace('/[^\d]/', '', (string) $priceRaw);

                if ($code === '' || $name === '') {
                    continue;
                }

                if (!isset($categoryMap[$categoryName]) && $categoryName !== '') {
                    $category = Category::firstOrCreate(['name' => $categoryName]);
                    $categoryMap[$categoryName] = $category;
                }

                $categoryId = isset($categoryMap[$categoryName]) ? $categoryMap[$categoryName]->id : 1;

                $menu = Menu::withTrashed()->updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $name,
                        'category_id' => $categoryId,
                        'price' => $price,
                        'is_active' => true,
                        'deleted_at' => null,
                    ]
                );

                $menuMap[$code] = $menu;
                $menuCount++;
            }

            $categoryCount = Category::count();

            // 2. Sheet Header_Transaksi
            $headerSheet = $this->getSheetByName($spreadsheet, ['Header_Transaksi', 'Header Transaksi', 'Header', 'header_transaksi']);
            if (!$headerSheet) {
                throw new \RuntimeException("Sheet Header_Transaksi tidak ditemukan dalam file Excel.");
            }

            $headerRows = $headerSheet->toArray(null, true, true, true);
            array_shift($headerRows); // remove header

            $transactionMap = []; // transaction_code => Transaction model id
            $transactionsToInsert = [];
            $transCount = 0;

            foreach ($headerRows as $row) {
                $trxCode = trim((string) ($row['A'] ?? ''));
                $rawDate = $row['B'] ?? null;
                // Column C is 'Hari' (ignored)
                $rawTime = $row['D'] ?? null;
                $totalItem = (int) ($row['E'] ?? 0);
                $totalAmount = (int) preg_replace('/[^\d]/', '', (string) ($row['F'] ?? 0));

                if ($trxCode === '') {
                    continue;
                }

                $trxDate = $this->parseExcelDate($rawDate);
                $trxTime = $this->parseExcelTime($rawTime);

                $transaction = Transaction::updateOrCreate(
                    ['transaction_code' => $trxCode],
                    [
                        'transaction_date' => $trxDate,
                        'transaction_time' => $trxTime,
                        'total_items' => $totalItem,
                        'total_amount' => $totalAmount,
                        'created_by' => $userId,
                    ]
                );

                $transactionMap[$trxCode] = $transaction->id;
                $transCount++;
            }

            // 3. Sheet Detail_Transaksi
            $detailSheet = $this->getSheetByName($spreadsheet, ['Detail_Transaksi', 'Detail Transaksi', 'Detail', 'detail_transaksi']);
            if (!$detailSheet) {
                throw new \RuntimeException("Sheet Detail_Transaksi tidak ditemukan dalam file Excel.");
            }

            $detailRows = $detailSheet->toArray(null, true, true, true);
            array_shift($detailRows); // remove header

            $itemCount = 0;
            $itemsBatch = [];
            $warnings = [];

            // Refresh menu map with latest IDs
            $menusByCode = Menu::all()->keyBy('code');

            foreach ($detailRows as $row) {
                $trxCode = trim((string) ($row['A'] ?? ''));
                $menuCode = trim((string) ($row['B'] ?? ''));
                // Column C is Menu Name (ignored according to PRD rule)
                $qty = (int) ($row['D'] ?? 1);
                $price = (int) preg_replace('/[^\d]/', '', (string) ($row['E'] ?? 0));
                $subtotal = (int) preg_replace('/[^\d]/', '', (string) ($row['F'] ?? 0));

                if ($trxCode === '' || $menuCode === '') {
                    continue;
                }

                $transactionId = $transactionMap[$trxCode] ?? null;
                if (!$transactionId) {
                    $warnings[] = "Transaksi {$trxCode} tidak ditemukan di master header transaksi.";
                    continue;
                }

                $menu = $menusByCode->get($menuCode);
                if (!$menu) {
                    $warnings[] = "Menu dengan kode {$menuCode} tidak ditemukan di master menu.";
                    continue;
                }

                $expectedSubtotal = $qty * $price;
                $discount = max(0, $expectedSubtotal - $subtotal);

                TransactionItem::updateOrCreate(
                    [
                        'transaction_id' => $transactionId,
                        'menu_id' => $menu->id,
                    ],
                    [
                        'qty' => $qty,
                        'price' => $price,
                        'discount' => $discount,
                        'subtotal' => $subtotal,
                    ]
                );

                $itemCount++;
            }

            // 4. Reconciliation Checks
            $reconciliation = $this->runReconciliation();
            if (!empty($reconciliation['warnings'])) {
                $warnings = array_merge($warnings, $reconciliation['warnings']);
            }

            DB::commit();

            // Update log
            $importLog->update([
                'status' => 'done',
                'rows_menu' => $menuCount,
                'rows_transactions' => $transCount,
                'rows_items' => $itemCount,
                'warnings' => $warnings,
                'finished_at' => now(),
            ]);

            return [
                'status' => 'success',
                'menus_count' => $menuCount,
                'categories_count' => $categoryCount,
                'transactions_count' => $transCount,
                'items_count' => $itemCount,
                'total_amount' => Transaction::sum('total_amount'),
                'warnings' => $warnings,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            $importLog->update([
                'status' => 'failed',
                'warnings' => [$e->getMessage()],
                'finished_at' => now(),
            ]);

            throw $e;
        }
    }

    /**
     * Run reconciliation checks after import.
     */
    public function runReconciliation(): array
    {
        $warnings = [];

        // Check header total_items vs sum of detail qty
        $mismatchedItems = DB::select("
            SELECT t.transaction_code, t.total_items, COALESCE(SUM(ti.qty), 0) as items_sum
            FROM transactions t
            LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
            GROUP BY t.id, t.transaction_code, t.total_items
            HAVING t.total_items != items_sum
        ");

        if (count($mismatchedItems) > 0) {
            $warnings[] = "Ditemukan " . count($mismatchedItems) . " transaksi dengan selisih total item antara header dan detail.";
        }

        // Check header total_amount vs sum of detail subtotal
        $mismatchedAmounts = DB::select("
            SELECT t.transaction_code, t.total_amount, COALESCE(SUM(ti.subtotal), 0) as amount_sum
            FROM transactions t
            LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
            GROUP BY t.id, t.transaction_code, t.total_amount
            HAVING t.total_amount != amount_sum
        ");

        if (count($mismatchedAmounts) > 0) {
            $warnings[] = "Ditemukan " . count($mismatchedAmounts) . " transaksi dengan selisih total bayar antara header dan detail.";
        }

        return [
            'valid' => empty($warnings),
            'warnings' => $warnings,
        ];
    }

    private function getSheetByName($spreadsheet, array $possibleNames)
    {
        foreach ($possibleNames as $name) {
            $sheet = $spreadsheet->getSheetByName($name);
            if ($sheet !== null) {
                return $sheet;
            }
        }
        return null;
    }

    private function parseExcelDate(mixed $rawDate): string
    {
        if (is_numeric($rawDate)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($rawDate))->format('Y-m-d');
        }

        if (empty($rawDate)) {
            return now()->format('Y-m-d');
        }

        try {
            return Carbon::parse($rawDate)->format('Y-m-d');
        } catch (\Throwable $e) {
            return now()->format('Y-m-d');
        }
    }

    private function parseExcelTime(mixed $rawTime): string
    {
        if (is_numeric($rawTime)) {
            // Excel fractional day
            $seconds = (int) round($rawTime * 86400);
            $h = floor($seconds / 3600);
            $m = floor(($seconds % 3600) / 60);
            $s = $seconds % 60;
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        }

        if (empty($rawTime)) {
            return '12:00:00';
        }

        try {
            return Carbon::parse($rawTime)->format('H:i:s');
        } catch (\Throwable $e) {
            return '12:00:00';
        }
    }
}
