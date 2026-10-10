<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 7 – Dashboard Analitik
 */
class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // categories table: only 'name' column (no 'code')
        $cat  = Category::create(['name' => 'Makanan']);
        $menu = Menu::create([
            'code'        => 'MKN001',
            'name'        => 'Ayam Goreng',
            'category_id' => $cat->id,
            'price'       => 15000,
            'is_active'   => true,
        ]);

        // transactions table uses 'transaction_code'
        $trx = Transaction::create([
            'transaction_code' => 'TRX2026100100001',
            'transaction_date' => now()->toDateString(),
            'transaction_time' => now()->format('H:i:s'),
            'total_items'      => 2,
            'total_amount'     => 30000,
            'created_by'       => $this->admin->id,
        ]);

        // transaction_items table uses 'qty'
        TransactionItem::create([
            'transaction_id' => $trx->id,
            'menu_id'        => $menu->id,
            'qty'            => 2,
            'price'          => 15000,
            'discount'       => 0,
            'subtotal'       => 30000,
        ]);
    }

    /** @test */
    public function dashboard_loads_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard.index');
        $response->assertViewHas('stats');
        $response->assertViewHas('trendDays');
        $response->assertViewHas('topMenus');
        $response->assertViewHas('topKategori');
    }

    /** @test */
    public function dashboard_summary_stats_match_database(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertOk();
        $stats = $response->viewData('stats');

        $this->assertEquals(30000, $stats['total_omzet']);
        $this->assertEquals(1,     $stats['total_transactions']);
        $this->assertEquals(2,     $stats['total_items']);
    }

    /** @test */
    public function dashboard_date_filter_returns_zero_outside_range(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard', [
                'date_from' => '2000-01-01',
                'date_to'   => '2000-01-31',
            ]));

        $response->assertOk();
        $stats = $response->viewData('stats');

        $this->assertEquals(0, $stats['total_omzet']);
        $this->assertEquals(0, $stats['total_transactions']);
    }

    /** @test */
    public function dashboard_is_inaccessible_when_unauthenticated(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }
}
