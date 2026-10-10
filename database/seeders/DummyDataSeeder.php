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
        // 1. Akun Pengguna
        $admin = User::updateOrCreate(
            ['email' => 'admin@kobana.id'],
            [
                'name' => 'Admin Kobana',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $kasir = User::updateOrCreate(
            ['email' => 'kasir@kobana.id'],
            [
                'name' => 'Siti Kasir',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'is_active' => true,
            ]
        );

        $owner = User::updateOrCreate(
            ['email' => 'owner@kobana.id'],
            [
                'name' => 'Bapak Owner',
                'password' => Hash::make('password'),
                'role' => 'owner',
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
                'is_active' => true,
            ]
        );
        User::updateOrCreate(
            ['email' => 'kasir@kobana.test'],
            [
                'name' => 'Kasir Kobana',
                'password' => Hash::make('kasir123'),
                'role' => 'kasir',
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

        $userIds = [$admin->id, $kasir->id];

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

                // Buat record Transaksi
                $trx = Transaction::create([
                    'transaction_code' => $code,
                    'transaction_date' => $dateStr,
                    'transaction_time' => $timeStr,
                    'total_items' => 0,
                    'total_amount' => 0,
                    'created_by' => $userIds[array_rand($userIds)],
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

                $trx->update([
                    'total_items' => $totalQty,
                    'total_amount' => $totalSum,
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
