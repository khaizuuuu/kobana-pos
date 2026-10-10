<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FpAnalysis;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private User $owner;
    private Category $category;
    private Menu $menu;
    private Transaction $transaction;
    private FpAnalysis $analysis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->kasir = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $this->owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);

        $this->category = Category::create(['name' => 'Ayam Goreng']);
        $this->menu = Menu::create([
            'code' => 'M01',
            'name' => 'Paket Dada Crispy',
            'category_id' => $this->category->id,
            'price' => 20000,
            'is_active' => true,
        ]);

        $this->transaction = Transaction::create([
            'transaction_code' => 'TRX2026101000001',
            'transaction_date' => now()->toDateString(),
            'transaction_time' => '12:00:00',
            'total_items' => 2,
            'total_amount' => 40000,
            'created_by' => $this->admin->id,
        ]);

        TransactionItem::create([
            'transaction_id' => $this->transaction->id,
            'menu_id' => $this->menu->id,
            'qty' => 2,
            'price' => 20000,
            'discount' => 0,
            'subtotal' => 40000,
        ]);

        $this->analysis = FpAnalysis::create([
            'name' => 'Analisis Uji Sidebar',
            'date_from' => now()->subDays(7)->toDateString(),
            'date_to' => now()->toDateString(),
            'min_support' => 0.05,
            'min_confidence' => 0.5,
            'status' => 'done',
            'total_transactions' => 1,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_access_all_sidebar_features(): void
    {
        $this->actingAs($this->admin);

        // 1. Dashboard
        $this->get(route('dashboard'))->assertOk()->assertSee('Dashboard Analitik Penjualan');

        // 2. POS Kasir / Input Transaksi
        $this->get(route('transactions.create'))->assertOk()->assertSee('Pilih Menu Aktif');

        // 3. Riwayat Transaksi
        $this->get(route('transactions.index'))->assertOk()->assertSee('TRX2026101000001');
        $this->get(route('transactions.show', $this->transaction))->assertOk()->assertSee('Rincian Nota Transaksi');
        $this->get(route('transactions.edit', $this->transaction))->assertOk()->assertSee('Edit Transaksi');

        // 4. Master Menu
        $this->get(route('menus.index'))->assertOk()->assertSee('Paket Dada Crispy');
        $this->get(route('menus.create'))->assertOk()->assertSee('Tambah Menu Baru');
        $this->get(route('menus.edit', $this->menu))->assertOk()->assertSee('Edit Menu');

        // 5. Master Kategori
        $this->get(route('categories.index'))->assertOk()->assertSee('Ayam Goreng');
        $this->get(route('categories.create'))->assertOk()->assertSee('Formulir Kategori Baru');
        $this->get(route('categories.edit', $this->category))->assertOk()->assertSee('Edit Kategori');

        // 6. Analisis FP-Growth
        $this->get(route('analysis.fp-growth'))->assertOk()->assertSee('Analisis Uji Sidebar');
        $this->get(route('analysis.fp-growth.show', $this->analysis))->assertOk()->assertSee('Hasil Analisis');

        // 7. Laporan Penjualan
        $this->get(route('reports.index'))->assertOk()->assertSee('Laporan Penjualan');

        // 8. Impor Dataset
        $this->get(route('imports.index'))->assertOk()->assertSee('Impor Dataset Penjualan');

        // 9. Manajemen User
        $this->get(route('users.index'))->assertOk()->assertSee('Daftar Pengguna Sistem');
        $this->get(route('users.create'))->assertOk()->assertSee('Tambah Pengguna Baru');
        $this->get(route('users.edit', $this->kasir))->assertOk()->assertSee('Edit Pengguna');
    }

    public function test_kasir_can_access_allowed_sidebar_features(): void
    {
        $this->actingAs($this->kasir);

        // Allowed
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('transactions.create'))->assertOk();
        $this->get(route('transactions.index'))->assertOk();
        $this->get(route('transactions.show', $this->transaction))->assertOk();
        $this->get(route('menus.index'))->assertOk();
        $this->get(route('categories.index'))->assertOk();

        // Forbidden
        $this->get(route('transactions.edit', $this->transaction))->assertForbidden();
        $this->get(route('menus.create'))->assertForbidden();
        $this->get(route('categories.create'))->assertForbidden();
        $this->get(route('analysis.fp-growth'))->assertForbidden();
        $this->get(route('reports.index'))->assertForbidden();
        $this->get(route('imports.index'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_owner_can_access_allowed_sidebar_features(): void
    {
        $this->actingAs($this->owner);

        // Allowed
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('transactions.index'))->assertOk();
        $this->get(route('transactions.show', $this->transaction))->assertOk();
        $this->get(route('menus.index'))->assertOk();
        $this->get(route('categories.index'))->assertOk();
        $this->get(route('analysis.fp-growth'))->assertOk();
        $this->get(route('analysis.fp-growth.show', $this->analysis))->assertOk();
        $this->get(route('reports.index'))->assertOk();

        // Forbidden
        $this->get(route('menus.create'))->assertForbidden();
        $this->get(route('categories.create'))->assertForbidden();
        $this->get(route('imports.index'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
    }
}
