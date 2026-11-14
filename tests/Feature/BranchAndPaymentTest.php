<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BranchAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch1;
    private Branch $branch2;
    private User $admin;
    private User $kasirCabang1;
    private User $kasirCabang2;
    private Menu $menu;

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

        $this->kasirCabang1 = User::factory()->create([
            'name' => 'Kasir Pusat',
            'role' => 'kasir',
            'branch_id' => $this->branch1->id,
            'is_active' => true,
        ]);

        $this->kasirCabang2 = User::factory()->create([
            'name' => 'Kasir Sukun',
            'role' => 'kasir',
            'branch_id' => $this->branch2->id,
            'is_active' => true,
        ]);

        $category = Category::create(['name' => 'Paket Ayam']);
        $this->menu = Menu::create([
            'code' => 'M01',
            'name' => 'Paket Geprek Kobana',
            'category_id' => $category->id,
            'price' => 18000,
            'is_active' => true,
        ]);
    }

    public function test_cashier_transaction_is_automatically_assigned_to_their_branch(): void
    {
        $cart = [
            [
                'menu_id' => $this->menu->id,
                'name' => $this->menu->name,
                'price' => 18000,
                'qty' => 2,
                'discount' => 0,
                'subtotal' => 36000,
            ]
        ];

        $response = $this->actingAs($this->kasirCabang1)->post(route('transactions.store'), [
            'cart' => json_encode($cart),
            'payment_method' => 'cash',
            'cash_amount' => 50000,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'branch_id' => $this->branch1->id,
            'created_by' => $this->kasirCabang1->id,
            'payment_method' => 'cash',
            'cash_amount' => 50000,
            'change_amount' => 14000,
            'total_amount' => 36000,
        ]);
    }

    public function test_cashier_can_pay_using_qris(): void
    {
        $cart = [
            [
                'menu_id' => $this->menu->id,
                'name' => $this->menu->name,
                'price' => 18000,
                'qty' => 1,
                'discount' => 0,
                'subtotal' => 18000,
            ]
        ];

        $response = $this->actingAs($this->kasirCabang1)->post(route('transactions.store'), [
            'cart' => json_encode($cart),
            'payment_method' => 'qris',
            'payment_note' => 'QRIS-REF-998877',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'branch_id' => $this->branch1->id,
            'payment_method' => 'qris',
            'payment_note' => 'QRIS-REF-998877',
            'total_amount' => 18000,
            'change_amount' => 0,
        ]);
    }

    public function test_cashier_is_scoped_to_their_own_branch_transactions(): void
    {
        // Transaction in Branch 1
        $trx1 = Transaction::create([
            'transaction_code' => 'TRX001',
            'transaction_date' => now()->toDateString(),
            'transaction_time' => '10:00:00',
            'total_items' => 1,
            'total_amount' => 18000,
            'payment_method' => 'cash',
            'branch_id' => $this->branch1->id,
            'created_by' => $this->kasirCabang1->id,
        ]);

        // Transaction in Branch 2
        $trx2 = Transaction::create([
            'transaction_code' => 'TRX002',
            'transaction_date' => now()->toDateString(),
            'transaction_time' => '11:00:00',
            'total_items' => 1,
            'total_amount' => 18000,
            'payment_method' => 'qris',
            'branch_id' => $this->branch2->id,
            'created_by' => $this->kasirCabang2->id,
        ]);

        // Kasir 1 should see TRX001 but not TRX002
        $response1 = $this->actingAs($this->kasirCabang1)->get(route('transactions.index'));
        $response1->assertOk();
        $response1->assertSee('TRX001');
        $response1->assertDontSee('TRX002');

        // Admin should see both
        $responseAdmin = $this->actingAs($this->admin)->get(route('transactions.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('TRX001');
        $responseAdmin->assertSee('TRX002');
    }

    public function test_admin_can_filter_transactions_by_payment_method_and_branch(): void
    {
        Transaction::create([
            'transaction_code' => 'TRX-CASH-B1',
            'transaction_date' => now()->toDateString(),
            'transaction_time' => '10:00:00',
            'total_items' => 1,
            'total_amount' => 18000,
            'payment_method' => 'cash',
            'branch_id' => $this->branch1->id,
            'created_by' => $this->admin->id,
        ]);

        Transaction::create([
            'transaction_code' => 'TRX-QRIS-B2',
            'transaction_date' => now()->toDateString(),
            'transaction_time' => '11:00:00',
            'total_items' => 1,
            'total_amount' => 18000,
            'payment_method' => 'qris',
            'branch_id' => $this->branch2->id,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('transactions.index', [
            'payment_method' => 'qris',
            'branch_id' => $this->branch2->id,
        ]));

        $response->assertOk();
        $response->assertSee('TRX-QRIS-B2');
        $response->assertDontSee('TRX-CASH-B1');
    }

    public function test_dashboard_displays_payment_breakdown_and_branch_filter(): void
    {
        Transaction::create([
            'transaction_code' => 'TRX-DASH-1',
            'transaction_date' => now()->toDateString(),
            'transaction_time' => '10:00:00',
            'total_items' => 1,
            'total_amount' => 20000,
            'payment_method' => 'cash',
            'branch_id' => $this->branch1->id,
            'created_by' => $this->admin->id,
        ]);

        Transaction::create([
            'transaction_code' => 'TRX-DASH-2',
            'transaction_date' => now()->toDateString(),
            'transaction_time' => '11:00:00',
            'total_items' => 1,
            'total_amount' => 30000,
            'payment_method' => 'qris',
            'branch_id' => $this->branch1->id,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Cash (Tunai)');
        $response->assertSee('QRIS');
        $response->assertDontSee('Transfer Bank');
        $response->assertSee('Kobana Pusat');
    }

    public function test_cashier_can_store_qris_transaction_with_camera_proof(): void
    {
        Storage::fake('public');

        // Simple 1x1 base64 transparent gif or jpeg
        $base64Image = 'data:image/jpeg;base64,' . base64_encode('fake-binary-image-data-from-camera');

        $cart = [
            [
                'menu_id' => $this->menu->id,
                'name' => $this->menu->name,
                'price' => 18000,
                'qty' => 1,
                'discount' => 0,
                'subtotal' => 18000,
            ]
        ];

        $response = $this->actingAs($this->kasirCabang1)->post(route('transactions.store'), [
            'cart' => json_encode($cart),
            'payment_method' => 'qris',
            'payment_note' => 'RRN-998877',
            'payment_proof_camera' => $base64Image,
        ]);

        $response->assertRedirect();

        $transaction = Transaction::where('payment_method', 'qris')->latest('id')->first();
        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->payment_proof);
        $this->assertStringStartsWith('payment_proofs/qris_', $transaction->payment_proof);
        Storage::disk('public')->assertExists($transaction->payment_proof);

        // Test show view contains proof image
        $showRes = $this->actingAs($this->kasirCabang1)->get(route('transactions.show', $transaction));
        $showRes->assertOk();
        $showRes->assertSee($transaction->payment_proof_url);
    }

    public function test_cashier_can_store_qris_transaction_with_uploaded_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('bukti_struk.jpg', 600, 800);

        $cart = [
            [
                'menu_id' => $this->menu->id,
                'name' => $this->menu->name,
                'price' => 18000,
                'qty' => 1,
                'discount' => 0,
                'subtotal' => 18000,
            ]
        ];

        $response = $this->actingAs($this->kasirCabang2)->post(route('transactions.store'), [
            'cart' => json_encode($cart),
            'payment_method' => 'qris',
            'payment_proof_file' => $file,
        ]);

        $response->assertRedirect();

        $transaction = Transaction::where('branch_id', $this->branch2->id)->latest('id')->first();
        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->payment_proof);
        Storage::disk('public')->assertExists($transaction->payment_proof);
    }

    public function test_admin_can_update_and_remove_payment_proof(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('bukti_awal.jpg');
        $storedPath = $file->store('payment_proofs', 'public');

        $transaction = Transaction::create([
            'transaction_code' => 'TRX-EDIT-PROOF',
            'transaction_date' => now()->toDateString(),
            'transaction_time' => '12:00:00',
            'total_items' => 1,
            'total_amount' => 18000,
            'payment_method' => 'qris',
            'payment_proof' => $storedPath,
            'branch_id' => $this->branch1->id,
            'created_by' => $this->admin->id,
        ]);

        Storage::disk('public')->assertExists($storedPath);

        $cart = [
            [
                'menu_id' => $this->menu->id,
                'name' => $this->menu->name,
                'price' => 18000,
                'qty' => 1,
                'discount' => 0,
                'subtotal' => 18000,
            ]
        ];

        $response = $this->actingAs($this->admin)->put(route('transactions.update', $transaction), [
            'cart' => json_encode($cart),
            'payment_method' => 'cash',
            'cash_amount' => 20000,
            'remove_payment_proof' => 1,
        ]);

        $response->assertRedirect();

        $transaction->refresh();
        $this->assertNull($transaction->payment_proof);
        Storage::disk('public')->assertMissing($storedPath);
    }
}
