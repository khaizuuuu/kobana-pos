<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data 3 Cabang Kobana
        $branch1 = \App\Models\Branch::updateOrCreate(
            ['code' => 'KBN01'],
            ['name' => 'Kobana Pusat', 'address' => 'Jl. Soekarno Hatta No. 12, Malang', 'phone' => '081234567801', 'is_active' => true]
        );
        $branch2 = \App\Models\Branch::updateOrCreate(
            ['code' => 'KBN02'],
            ['name' => 'Kobana Sukun', 'address' => 'Jl. S. Supriadi No. 45, Malang', 'phone' => '081234567802', 'is_active' => true]
        );
        $branch3 = \App\Models\Branch::updateOrCreate(
            ['code' => 'KBN03'],
            ['name' => 'Kobana Dinoyo', 'address' => 'Jl. MT Haryono No. 88, Malang', 'phone' => '081234567803', 'is_active' => true]
        );
        $branches = [$branch1, $branch2, $branch3];

        // 2. Akun Pengguna
        $admin = User::updateOrCreate(
            ['email' => 'admin@kobana.id'],
            [
                'name' => 'Admin Kobana',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'branch_id' => null, // Akses semua cabang
                'is_active' => true,
            ]
        );

        $kasir1 = User::updateOrCreate(
            ['email' => 'kasir@kobana.id'],
            [
                'name' => 'Siti Kasir (Pusat)',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'branch_id' => $branch1->id,
                'is_active' => true,
            ]
        );

        $kasir2 = User::updateOrCreate(
            ['email' => 'kasir2@kobana.id'],
            [
                'name' => 'Budi Kasir (Sukun)',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'branch_id' => $branch2->id,
                'is_active' => true,
            ]
        );

        $kasir3 = User::updateOrCreate(
            ['email' => 'kasir3@kobana.id'],
            [
                'name' => 'Dewi Kasir (Dinoyo)',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'branch_id' => $branch3->id,
                'is_active' => true,
            ]
        );

        $owner = User::updateOrCreate(
            ['email' => 'owner@kobana.id'],
            [
                'name' => 'Bapak Owner',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'branch_id' => null, // Akses semua cabang
                'is_active' => true,
            ]
        );

        // Akun alternatif .test
        User::updateOrCreate(
            ['email' => 'admin@kobana.test'],
            [
                'name' => 'Admin Kobana',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'branch_id' => null,
                'is_active' => true,
            ]
        );
        User::updateOrCreate(
            ['email' => 'kasir@kobana.test'],
            [
                'name' => 'Kasir Kobana (Pusat)',
                'password' => Hash::make('kasir123'),
                'role' => 'kasir',
                'branch_id' => $branch1->id,
                'is_active' => true,
            ]
        );
        User::updateOrCreate(
            ['email' => 'owner@kobana.test'],
            [
                'name' => 'Owner Kobana',
                'password' => Hash::make('owner123'),
                'role' => 'owner',
                'is_active' => true,
            ]
        );

        // 2. Kategori Produk
        $categoriesData = [
            'Paket Nasi & Ayam',
            'Ayam Crispy Satuan',
            'Burger & Roti',
            'Snack & Side Dish',
            'Minuman Dingin',
            'Ekstra Saus & Sambal',
        ];

        $categories = [];
        foreach ($categoriesData as $catName) {
            $categories[$catName] = Category::firstOrCreate(['name' => $catName]);
        }

        // 3. Master Menu Produk
        $menusData = [
            // Paket Nasi & Ayam
            ['code' => 'M01', 'name' => 'Paket Dada Crispy + Nasi', 'category' => 'Paket Nasi & Ayam', 'price' => 22000],
            ['code' => 'M02', 'name' => 'Paket Paha Atas + Nasi', 'category' => 'Paket Nasi & Ayam', 'price' => 21000],
            ['code' => 'M03', 'name' => 'Paket Sayap Crispy + Nasi', 'category' => 'Paket Nasi & Ayam', 'price' => 18000],
            ['code' => 'M04', 'name' => 'Paket Ayam Geprek Sambal Bawang', 'category' => 'Paket Nasi & Ayam', 'price' => 24000],
            ['code' => 'M05', 'name' => 'Paket Ayam Saus BBQ Spesial', 'category' => 'Paket Nasi & Ayam', 'price' => 25000],
            ['code' => 'M06', 'name' => 'Paket Ayam Saus Keju Lumer', 'category' => 'Paket Nasi & Ayam', 'price' => 26000],

            // Ayam Crispy Satuan
            ['code' => 'M07', 'name' => 'Ayam Dada Crispy Satuan', 'category' => 'Ayam Crispy Satuan', 'price' => 16000],
            ['code' => 'M08', 'name' => 'Ayam Paha Bawah Satuan', 'category' => 'Ayam Crispy Satuan', 'price' => 13000],

            // Burger & Roti
            ['code' => 'M09', 'name' => 'Kobana Chicken Burger', 'category' => 'Burger & Roti', 'price' => 19000],
            ['code' => 'M10', 'name' => 'Kobana Double Cheese Burger', 'category' => 'Burger & Roti', 'price' => 25000],

            // Snack & Side Dish
            ['code' => 'M11', 'name' => 'French Fries Large', 'category' => 'Snack & Side Dish', 'price' => 14000],
            ['code' => 'M12', 'name' => 'Nugget Crispy 5 pcs', 'category' => 'Snack & Side Dish', 'price' => 15000],
            ['code' => 'M13', 'name' => 'Jamur Crispy Gurih', 'category' => 'Snack & Side Dish', 'price' => 12000],
            ['code' => 'M14', 'name' => 'Nasi Putih Pulen', 'category' => 'Snack & Side Dish', 'price' => 5000],

            // Minuman Dingin
            ['code' => 'M15', 'name' => 'Es Teh Manis Jumbo', 'category' => 'Minuman Dingin', 'price' => 5000],
            ['code' => 'M16', 'name' => 'Es Jeruk Peras Segar', 'category' => 'Minuman Dingin', 'price' => 7000],
            ['code' => 'M17', 'name' => 'Coca-Cola Can 330ml', 'category' => 'Minuman Dingin', 'price' => 8000],
            ['code' => 'M18', 'name' => 'Air Mineral Botol 600ml', 'category' => 'Minuman Dingin', 'price' => 4000],

            // Ekstra Saus & Sambal
            ['code' => 'M19', 'name' => 'Ekstra Sambal Korek Pedas', 'category' => 'Ekstra Saus & Sambal', 'price' => 3000],
            ['code' => 'M20', 'name' => 'Ekstra Saus Keju', 'category' => 'Ekstra Saus & Sambal', 'price' => 4000],
        ];

        $menusByCode = [];
        foreach ($menusData as $m) {
            $catId = $categories[$m['category']]->id;
            $menu = Menu::updateOrCreate(
                ['code' => $m['code']],
                [
                    'name' => $m['name'],
                    'category_id' => $catId,
                    'price' => $m['price'],
                    'is_active' => true,
                ]
            );
            $menusByCode[$m['code']] = $menu;
        }

        // 4. Pola Basket Transaksi untuk Analisis FP-Growth (Market Basket Analysis)
        // Dibuat kombinasi menu yang realistis sehingga menghasilkan Lift & Confidence tinggi
        $basketTemplates = [
            // Pola 1: Geprek + Es Teh + Sambal Korek (Korelasi Kuat)
            ['codes' => ['M04', 'M15', 'M19'], 'weight' => 25],
            // Pola 2: Dada Crispy + Es Teh (Favorit umum)
            ['codes' => ['M01', 'M15'], 'weight' => 35],
            // Pola 3: Burger + Kentang Goreng + Cola (Paket Barat)
            ['codes' => ['M09', 'M11', 'M17'], 'weight' => 20],
            // Pola 4: Double Cheese Burger + French Fries (Paket Burger)
            ['codes' => ['M10', 'M11', 'M15'], 'weight' => 15],
            // Pola 5: Ayam Saus BBQ + Nasi + Es Jeruk
            ['codes' => ['M05', 'M16'], 'weight' => 18],
            // Pola 6: Ayam Saus Keju + French Fries + Es Teh
            ['codes' => ['M06', 'M11', 'M15'], 'weight' => 16],
            // Pola 7: Paha Atas + Es Teh Manis
            ['codes' => ['M02', 'M15'], 'weight' => 22],
            // Pola 8: Sayap + Jamur Crispy + Air Mineral
            ['codes' => ['M03', 'M13', 'M18'], 'weight' => 14],
            // Pola 9: Dada Satuan + Nasi Putih + Es Teh + Ekstra Saus Keju
            ['codes' => ['M07', 'M14', 'M15', 'M20'], 'weight' => 12],
            // Pola 10: Snack time (Nugget + Kentang + Es Jeruk)
            ['codes' => ['M11', 'M12', 'M16'], 'weight' => 15],
            // Pola 11: Paha Bawah + Nasi + Air Mineral
            ['codes' => ['M08', 'M14', 'M18'], 'weight' => 10],
        ];

        // 5. Generate 180 Transaksi dalam 30 Hari Terakhir
        $now = Carbon::now();
        $startDate = $now->copy()->subDays(29)->startOfDay();
        $trxPerDayCount = [];

        // Hapus transaksi lama jika ada untuk idempotensi seeder
        DB::table('transaction_items')->delete();
        DB::table('transactions')->delete();
        DB::table('transaction_sequences')->delete();

        $cashiers = [$kasir1, $kasir2, $kasir3];

        // Loop 30 hari ke belakang
        for ($d = 0; $d < 30; $d++) {
            $currentDate = $startDate->copy()->addDays($d);
            $dateStr = $currentDate->toDateString();
            
            // Variasi jumlah transaksi per hari (5 - 9 transaksi per hari, weekend lebih ramai)
            $isWeekend = in_array($currentDate->dayOfWeek, [0, 6]);
            $numTrxToday = $isWeekend ? rand(7, 10) : rand(4, 7);

            $seqToday = 0;

            for ($t = 0; $t < $numTrxToday; $t++) {
                $seqToday++;
                $code = 'TRX' . $currentDate->format('Ymd') . sprintf('%05d', $seqToday);

                // Jam transaksi (antara 10:00 sampai 21:30, lebih sering di jam makan siang 12-14 & malam 18-20)
                $peakHour = rand(0, 10) > 4 ? (rand(0, 1) ? rand(11, 13) : rand(18, 20)) : rand(10, 21);
                $minute = rand(0, 59);
                $second = rand(0, 59);
                $timeStr = sprintf('%02d:%02d:%02d', $peakHour, $minute, $second);

                // Pilih template basket secara berbobot
                $selectedTemplate = $this->getRandomWeightedTemplate($basketTemplates);
                $selectedCodes = $selectedTemplate['codes'];

                // Kasir dan Cabang acak (Pusat, Sukun, Dinoyo)
                $assignedKasir = $cashiers[array_rand($cashiers)];

                // Metode pembayaran realistis: 60% Cash, 40% QRIS
                $randPay = rand(1, 100);
                $payMethod = $randPay <= 60 ? 'cash' : 'qris';

                // Buat record Transaksi
                $trx = Transaction::create([
                    'transaction_code' => $code,
                    'transaction_date' => $dateStr,
                    'transaction_time' => $timeStr,
                    'total_items' => 0,
                    'total_amount' => 0,
                    'branch_id' => $assignedKasir->branch_id,
                    'payment_method' => $payMethod,
                    'cash_amount' => 0,
                    'change_amount' => 0,
                    'payment_note' => null,
                    'created_by' => $assignedKasir->id,
                    'created_at' => $currentDate->copy()->setTime($peakHour, $minute, $second),
                    'updated_at' => $currentDate->copy()->setTime($peakHour, $minute, $second),
                ]);

                $totalQty = 0;
                $totalSum = 0;

                foreach ($selectedCodes as $menuCode) {
                    if (!isset($menusByCode[$menuCode])) continue;
                    $menuObj = $menusByCode[$menuCode];
                    $qty = (rand(1, 10) > 8) ? 2 : 1; // Sesekali beli 2 porsi
                    $price = $menuObj->price;
                    $discount = (rand(1, 20) === 20) ? 2000 : 0; // Jarang diskon promo
                    $subtotal = max(0, ($price * $qty) - $discount);

                    TransactionItem::create([
                        'transaction_id' => $trx->id,
                        'menu_id' => $menuObj->id,
                        'qty' => $qty,
                        'price' => $price,
                        'discount' => $discount,
                        'subtotal' => $subtotal,
                    ]);

                    $totalQty += $qty;
                    $totalSum += $subtotal;
                }

                // Kalkulasi uang tunai & kembalian sesuai metode pembayaran
                if ($payMethod === 'cash') {
                    $cashAmount = (int) (ceil($totalSum / 10000) * 10000);
                    if ($cashAmount < $totalSum) $cashAmount += 10000;
                    if (rand(1, 3) === 1 && $totalSum <= 40000) $cashAmount = 50000;
                    if (rand(1, 3) === 1 && $totalSum <= 80000) $cashAmount = 100000;
                    $changeAmount = $cashAmount - $totalSum;
                    $paymentNote = null;
                } else { // qris
                    $cashAmount = $totalSum;
                    $changeAmount = 0;
                    $paymentNote = 'NMID: 93600' . rand(100000, 999999);
                }

                $trx->update([
                    'total_items' => $totalQty,
                    'total_amount' => $totalSum,
                    'cash_amount' => $cashAmount,
                    'change_amount' => $changeAmount,
                    'payment_note' => $paymentNote,
                ]);
            }

            // Catat sequence harian
            DB::table('transaction_sequences')->insert([
                'date' => $dateStr,
                'next_seq' => $seqToday,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5.5. Buat Catatan Kas Operasional Harian (Belanja Warung & Tabungan Kasir)
        DB::table('operational_cashes')->delete();

        $expenseTemplates = [
            ['name' => 'Beras 1 Karung (25kg)', 'category' => 'Bahan Baku', 'amount' => 340000, 'notes' => 'Toko Beras Barokah'],
            ['name' => 'Beras 10kg Super', 'category' => 'Bahan Baku', 'amount' => 140000, 'notes' => 'Beli di pasar induk'],
            ['name' => 'Refill Gas LPG 3kg (2 tabung)', 'category' => 'Gas & Energi', 'amount' => 42000, 'notes' => 'Pangkalan Gas Sumber Rejeki'],
            ['name' => 'Refill Gas LPG 3kg (1 tabung)', 'category' => 'Gas & Energi', 'amount' => 21000, 'notes' => 'Warung terdekat'],
            ['name' => 'Kantong Kresek Bening & Hitam (3 pack)', 'category' => 'Kemasan & Plastik', 'amount' => 28000, 'notes' => 'Toko Plastik Sentosa'],
            ['name' => 'Minyak Goreng SunCo 2L (2 pouch)', 'category' => 'Bahan Baku', 'amount' => 76000, 'notes' => 'Grosir minyak goreng'],
            ['name' => 'Gula Pasir 2kg & Teh Celup Jumbo', 'category' => 'Bahan Baku', 'amount' => 38000, 'notes' => 'Stok teh manis pelanggan'],
            ['name' => 'Sabun Cuci Piring Sunlight 750ml & Spons', 'category' => 'Kebersihan & Sanitasi', 'amount' => 24000, 'notes' => 'Kebersihan peralatan dapur'],
            ['name' => 'Es Batu Kristal Higienis (3 sak)', 'category' => 'Bahan Baku', 'amount' => 24000, 'notes' => 'Depot Es Kristal'],
        ];

        for ($dayOffset = 0; $dayOffset < 30; $dayOffset++) {
            $curDate = $startDate->copy()->addDays($dayOffset);
            $curDateStr = $curDate->toDateString();

            foreach ($cashiers as $kasir) {
                // Belanja harian kebutuhan warung (1-2 item)
                $numExpenses = rand(1, 2);
                for ($e = 0; $e < $numExpenses; $e++) {
                    $tpl = $expenseTemplates[array_rand($expenseTemplates)];
                    \App\Models\OperationalCash::create([
                        'date' => $curDateStr,
                        'time' => sprintf('%02d:%02d:00', rand(8, 14), rand(0, 59)),
                        'branch_id' => $kasir->branch_id,
                        'user_id' => $kasir->id,
                        'type' => 'pengeluaran',
                        'category' => $tpl['category'],
                        'name' => $tpl['name'],
                        'amount' => $tpl['amount'],
                        'notes' => $tpl['notes'],
                    ]);
                }

                // Tabungan harian kasir
                $tabunganAmount = (rand(3, 8) * 50000);
                \App\Models\OperationalCash::create([
                    'date' => $curDateStr,
                    'time' => sprintf('%02d:%02d:00', rand(20, 22), rand(0, 59)),
                    'branch_id' => $kasir->branch_id,
                    'user_id' => $kasir->id,
                    'type' => 'tabungan',
                    'category' => 'Tabungan Harian',
                    'name' => 'Setoran Tabungan Penjualan Harian',
                    'amount' => $tabunganAmount,
                    'notes' => 'Disisihkan ke tabungan kas warung',
                ]);
            }
        }

        // 6. Jalankan 1 Analisis FP-Growth Awal sebagai Contoh Hasil
        DB::table('fp_association_rules')->delete();
        DB::table('fp_frequent_itemsets')->delete();
        DB::table('fp_analyses')->delete();

        $analysis = \App\Models\FpAnalysis::create([
            'name' => 'Analisis Contoh Pola Bundling Menu (30 Hari)',
            'date_from' => $startDate->toDateString(),
            'date_to' => $now->toDateString(),
            'min_support' => 0.05,
            'min_confidence' => 0.50,
            'status' => 'pending',
            'created_by' => $admin->id,
            'started_at' => now(),
        ]);

        $fpService = new \App\Services\FpGrowthService(0.05, 0.50);
        $fpService->setDateRange($startDate, $now)->run($analysis);

        $analysis->update([
            'status' => 'done',
            'finished_at' => now(),
        ]);
    }

    /**
     * Memilih template basket berdasarkan bobot kemunculan.
     */
    private function getRandomWeightedTemplate(array $templates): array
    {
        $totalWeight = array_sum(array_column($templates, 'weight'));
        $rand = rand(1, $totalWeight);
        $current = 0;

        foreach ($templates as $t) {
            $current += $t['weight'];
            if ($rand <= $current) {
                return $t;
            }
        }

        return $templates[0];
    }
}
