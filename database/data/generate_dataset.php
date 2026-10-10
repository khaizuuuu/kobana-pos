<?php

require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();

// 1. Sheet Master_Menu
$sheetMenu = $spreadsheet->getActiveSheet();
$sheetMenu->setTitle('Master_Menu');

$menus = [
    ['M01', 'Paket Dada Komplit', 'Paket Ayam', 25000],
    ['M02', 'Paket Paha Atas Komplit', 'Paket Ayam', 24000],
    ['M03', 'Paket Sayap Komplit', 'Paket Ayam', 20000],
    ['M04', 'Paket Paha Bawah Komplit', 'Paket Ayam', 21000],
    ['M05', 'Ayam Dada Crispy', 'Ala Carte Ayam', 16000],
    ['M06', 'Ayam Paha Atas Crispy', 'Ala Carte Ayam', 15000],
    ['M07', 'Ayam Sayap Crispy', 'Ala Carte Ayam', 12000],
    ['M08', 'Ayam Paha Bawah Crispy', 'Ala Carte Ayam', 13000],
    ['M09', 'Ayam Spicy Dada', 'Ala Carte Ayam', 17000],
    ['M10', 'Nasi Putih', 'Nasi & Karbo', 5000],
    ['M11', 'French Fries', 'Nasi & Karbo', 10000],
    ['M12', 'Chicken Burger', 'Burger & Snack', 18000],
    ['M13', 'Chicken Nugget 6pcs', 'Burger & Snack', 15000],
    ['M14', 'Kulit Ayam Crispy', 'Burger & Snack', 12000],
    ['M15', 'Es Teh Manis', 'Minuman Dingin', 4000],
    ['M16', 'Es Jeruk', 'Minuman Dingin', 5000],
    ['M17', 'Air Mineral 600ml', 'Minuman Dingin', 4000],
    ['M18', 'Kopi Susu Gula Aren', 'Minuman Dingin', 12000],
    ['M19', 'Sambal Bawang Extra', 'Saus & Sambal', 3000],
];

$sheetMenu->fromArray([['Kode', 'Menu', 'Kategori', 'Harga']], null, 'A1');
$rowNum = 2;
foreach ($menus as $m) {
    $sheetMenu->fromArray([$m], null, 'A' . $rowNum);
    $rowNum++;
}

// 2. Generate Transactions for 1 April - 30 June 2026 (Period in PRD)
$startDate = strtotime('2026-04-01');
$endDate = strtotime('2026-06-30');
$days = round(($endDate - $startDate) / 86400) + 1; // 91 days

$sheetHeader = $spreadsheet->createSheet();
$sheetHeader->setTitle('Header_Transaksi');
$sheetHeader->fromArray([['ID', 'Tanggal', 'Hari', 'Jam', 'Total_Item', 'Total_Bayar']], null, 'A1');

$sheetDetail = $spreadsheet->createSheet();
$sheetDetail->setTitle('Detail_Transaksi');
$sheetDetail->fromArray([['ID', 'Kode', 'Menu', 'Qty', 'Harga', 'Subtotal']], null, 'A1');

$sheetFp = $spreadsheet->createSheet();
$sheetFp->setTitle('Data_FPGrowth');
$sheetFp->fromArray([['Transaction_ID', 'Item']], null, 'A1');

$headerRow = 2;
$detailRow = 2;
$fpRow = 2;

$trxCounter = 1;
$totalTargetTransactions = 3230;
$trxPerDayAvg = ceil($totalTargetTransactions / $days);

mt_srand(42); // deterministic

$daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Popular bundles for realistic FP-Growth associations:
// 1. M05 (Ayam Dada) + M10 (Nasi Putih) + M15 (Es Teh Manis)
// 2. M01 (Paket Dada) + M15 (Es Teh Manis)
// 3. M11 (French Fries) + M12 (Chicken Burger) + M16 (Es Jeruk)
// 4. M14 (Kulit Crispy) + M19 (Sambal Bawang)

$currentTrx = 1;
for ($d = 0; $d < $days && $currentTrx <= $totalTargetTransactions; $d++) {
    $currentTimestamp = $startDate + ($d * 86400);
    $dateStr = date('Y-m-d', $currentTimestamp);
    $dayName = $daysOfWeek[date('w', $currentTimestamp)];

    $numTrxToday = ($d == $days - 1) ? ($totalTargetTransactions - $currentTrx + 1) : mt_rand(28, 42);

    for ($t = 0; $t < $numTrxToday && $currentTrx <= $totalTargetTransactions; $t++) {
        $trxCode = 'TRX' . date('Ymd', $currentTimestamp) . sprintf('%05d', $currentTrx);
        
        $hour = mt_rand(10, 20);
        $min = mt_rand(0, 59);
        $sec = mt_rand(0, 59);
        $timeStr = sprintf('%02d:%02d:%02d', $hour, $min, $sec);

        // Pick 1 to 4 distinct items
        $bundleRoll = mt_rand(1, 100);
        $selectedMenuKeys = [];

        if ($bundleRoll <= 35) {
            // Bundle 1: Dada + Nasi + Es Teh
            $selectedMenuKeys = [4, 9, 14]; // M05, M10, M15
        } elseif ($bundleRoll <= 60) {
            // Bundle 2: Paket Dada + Es Teh
            $selectedMenuKeys = [0, 14]; // M01, M15
        } elseif ($bundleRoll <= 80) {
            // Bundle 3: Burger + Fries + Es Jeruk
            $selectedMenuKeys = [11, 10, 15]; // M12, M11, M16
        } else {
            // Random items
            $itemCountPick = mt_rand(1, 3);
            $keys = array_rand($menus, $itemCountPick);
            $selectedMenuKeys = is_array($keys) ? $keys : [$keys];
        }

        $trxTotalItems = 0;
        $trxTotalAmount = 0;

        foreach ($selectedMenuKeys as $mKey) {
            $mItem = $menus[$mKey];
            $qty = mt_rand(1, 2);
            $price = $mItem[3];
            $subtotal = $qty * $price;

            // Introduce occasional 1000 discount (as noted in PRD)
            if ($currentTrx % 215 == 0) {
                $subtotal = max(0, $subtotal - 1000);
            }

            $trxTotalItems += $qty;
            $trxTotalAmount += $subtotal;

            $sheetDetail->fromArray([[
                $trxCode,
                $mItem[0],
                $mItem[1],
                $qty,
                $price,
                $subtotal
            ]], null, 'A' . $detailRow);
            $detailRow++;

            $sheetFp->fromArray([[
                $trxCode,
                $mItem[1]
            ]], null, 'A' . $fpRow);
            $fpRow++;
        }

        $sheetHeader->fromArray([[
            $trxCode,
            $dateStr,
            $dayName,
            $timeStr,
            $trxTotalItems,
            $trxTotalAmount
        ]], null, 'A' . $headerRow);
        $headerRow++;

        $currentTrx++;
    }
}

$targetDir = __DIR__;
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$filePath = $targetDir . '/kobana_dataset.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filePath);

// Also copy to root directory for easy access
copy($filePath, dirname(__DIR__, 2) . '/kobana_dataset.xlsx');

echo "Dataset berhasil dibuat di {$filePath} dengan " . ($headerRow - 2) . " transaksi dan " . ($detailRow - 2) . " detail items.\n";
