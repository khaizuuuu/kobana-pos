<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\OperationalCash;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalCashTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch1;
    private Branch $branch2;
    private User $admin;
    private User $kasir1;
    private User $kasir2;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch1 = Branch::create([
            'code' => 'KBN01',
            'name' => 'Kobana Pusat',
            'address' => 'Jl. Soekarno Hatta No. 12',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'code' => 'KBN02',
            'name' => 'Kobana Sukun',
            'address' => 'Jl. S. Supriadi No. 45',
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->kasir1 = User::factory()->create([
            'name' => 'Kasir Pusat',
            'role' => 'kasir',
            'branch_id' => $this->branch1->id,
            'is_active' => true,
        ]);

        $this->kasir2 = User::factory()->create([
            'name' => 'Kasir Sukun',
            'role' => 'kasir',
            'branch_id' => $this->branch2->id,
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner Kobana',
            'role' => 'owner',
            'is_active' => true,
        ]);
    }

    public function test_cashier_can_record_daily_warung_expenses(): void
    {
        $response = $this->actingAs($this->kasir1)->post(route('operational-cash.store'), [
            'date'     => now()->toDateString(),
            'time'     => '09:00',
            'type'     => 'pengeluaran',
            'category' => 'Bahan Baku',
            'name'     => 'Beras 1 Karung 25kg',
            'amount'   => 340000,
            'notes'    => 'Beli di Toko Barokah',
        ]);

        $response->assertRedirect(route('operational-cash.index'));

        $this->assertDatabaseHas('operational_cashes', [
            'branch_id' => $this->branch1->id,
            'user_id'   => $this->kasir1->id,
            'type'      => 'pengeluaran',
            'category'  => 'Bahan Baku',
            'name'      => 'Beras 1 Karung 25kg',
            'amount'    => 340000,
        ]);
    }

    public function test_cashier_can_record_daily_savings(): void
    {
        $response = $this->actingAs($this->kasir1)->post(route('operational-cash.store'), [
            'date'     => now()->toDateString(),
            'time'     => '21:00',
            'type'     => 'tabungan',
            'category' => 'Tabungan Harian',
            'name'     => 'Setoran Tabungan Penjualan Harian',
            'amount'   => 250000,
            'notes'    => 'Disimpan di brankas kasir',
        ]);

        $response->assertRedirect(route('operational-cash.index'));

        $this->assertDatabaseHas('operational_cashes', [
            'branch_id' => $this->branch1->id,
            'user_id'   => $this->kasir1->id,
            'type'      => 'tabungan',
            'category'  => 'Tabungan Harian',
            'name'      => 'Setoran Tabungan Penjualan Harian',
            'amount'    => 250000,
        ]);
    }

    public function test_cashier_view_is_scoped_to_their_branch(): void
    {
        OperationalCash::create([
            'date'      => now()->toDateString(),
            'branch_id' => $this->branch1->id,
            'user_id'   => $this->kasir1->id,
            'type'      => 'pengeluaran',
            'name'      => 'Gas LPG 3kg Pusat',
            'amount'    => 21000,
        ]);

        OperationalCash::create([
            'date'      => now()->toDateString(),
            'branch_id' => $this->branch2->id,
            'user_id'   => $this->kasir2->id,
            'type'      => 'pengeluaran',
            'name'      => 'Gas LPG 3kg Sukun',
            'amount'    => 21000,
        ]);

        $response = $this->actingAs($this->kasir1)->get(route('operational-cash.index'));
        $response->assertOk();
        $response->assertSee('Gas LPG 3kg Pusat');
        $response->assertDontSee('Gas LPG 3kg Sukun');
    }

    public function test_owner_and_admin_can_view_all_branches_operational_cash(): void
    {
        OperationalCash::create([
            'date'      => now()->toDateString(),
            'branch_id' => $this->branch1->id,
            'user_id'   => $this->kasir1->id,
            'type'      => 'pengeluaran',
            'name'      => 'Kantong Kresek Pusat',
            'amount'    => 25000,
        ]);

        OperationalCash::create([
            'date'      => now()->toDateString(),
            'branch_id' => $this->branch2->id,
            'user_id'   => $this->kasir2->id,
            'type'      => 'pengeluaran',
            'name'      => 'Kantong Kresek Sukun',
            'amount'    => 30000,
        ]);

        $responseOwner = $this->actingAs($this->owner)->get(route('operational-cash.index'));
        $responseOwner->assertOk();
        $responseOwner->assertSee('Kantong Kresek Pusat');
        $responseOwner->assertSee('Kantong Kresek Sukun');
    }

    public function test_dashboard_displays_operational_cash_summary(): void
    {
        OperationalCash::create([
            'date'      => now()->toDateString(),
            'branch_id' => $this->branch1->id,
            'user_id'   => $this->kasir1->id,
            'type'      => 'pengeluaran',
            'name'      => 'Minyak Goreng',
            'amount'    => 75000,
        ]);

        OperationalCash::create([
            'date'      => now()->toDateString(),
            'branch_id' => $this->branch1->id,
            'user_id'   => $this->kasir1->id,
            'type'      => 'tabungan',
            'name'      => 'Tabungan Kasir',
            'amount'    => 200000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Total Belanja Operasional Warung');
        $response->assertSee('Total Tabungan / Setor Kas');
    }
}
