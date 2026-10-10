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
 * Phase 9 – Reports Feature Tests.
 */
class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->kasir = User::factory()->create(['role' => 'kasir', 'is_active' => true]);

        // categories table has no 'code' column
        $cat  = Category::create(['name' => 'Minuman']);
        $menu = Menu::create([
            'code'        => 'MNM001',
            'name'        => 'Es Teh',
            'category_id' => $cat->id,
            'price'       => 5000,
            'is_active'   => true,
        ]);

        // transactions table uses 'transaction_code', not 'code'
        $trx = Transaction::create([
            'transaction_code' => 'TRX2026100100001',
            'transaction_date' => now()->toDateString(),
            'transaction_time' => '10:00:00',
            'total_items'      => 3,
            'total_amount'     => 15000,
            'created_by'       => $this->admin->id,
        ]);

        // transaction_items table uses 'qty', not 'quantity'
        TransactionItem::create([
            'transaction_id' => $trx->id,
            'menu_id'        => $menu->id,
            'qty'            => 3,
            'price'          => 5000,
            'discount'       => 0,
            'subtotal'       => 15000,
        ]);
    }

    /** @test */
    public function admin_can_view_reports_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertViewIs('reports.index')
            ->assertViewHas('totalOmzet')
            ->assertViewHas('daily')
            ->assertViewHas('menuReport');
    }

    /** @test */
    public function kasir_cannot_access_reports(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('reports.index'))
            ->assertForbidden();
    }

    /** @test */
    public function export_csv_returns_csv_download(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.export-excel', [
                'date_from' => now()->toDateString(),
                'date_to'   => now()->toDateString(),
            ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function report_date_filter_works_correctly(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.index', [
                'date_from' => now()->toDateString(),
                'date_to'   => now()->toDateString(),
            ]));

        $response->assertOk();
        $this->assertEquals(15000, $response->viewData('totalOmzet'));
        $this->assertEquals(1, $response->viewData('totalTrx'));
    }
}
