<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_menus_and_categories(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $category = Category::create(['name' => 'Ayam']);
        $menu = Menu::create([
            'code' => 'M01',
            'name' => 'Paket Dada',
            'category_id' => $category->id,
            'price' => 25000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($kasir)->get('/menus');
        $response->assertStatus(200);
        $response->assertSee('Paket Dada');

        $responseCat = $this->actingAs($kasir)->get('/categories');
        $responseCat->assertStatus(200);
        $responseCat->assertSee('Ayam');
    }

    public function test_admin_can_create_menu(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = Category::create(['name' => 'Minuman']);

        $response = $this->actingAs($admin)->post('/menus', [
            'code' => 'M99',
            'name' => 'Jus Alpukat',
            'category_id' => $category->id,
            'price' => '15.000',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/menus');
        $this->assertDatabaseHas('menus', [
            'code' => 'M99',
            'name' => 'Jus Alpukat',
            'price' => 15000,
        ]);
    }

    public function test_kasir_cannot_create_or_delete_menu(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $category = Category::create(['name' => 'Minuman']);
        $menu = Menu::create([
            'code' => 'M01',
            'name' => 'Paket Dada',
            'category_id' => $category->id,
            'price' => 25000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($kasir)->post('/menus', [
            'code' => 'M99',
            'name' => 'Jus Alpukat',
            'category_id' => $category->id,
            'price' => 15000,
        ]);
        $response->assertStatus(403);

        $responseDel = $this->actingAs($kasir)->delete("/menus/{$menu->id}");
        $responseDel->assertStatus(403);
    }

    public function test_menu_with_transactions_is_soft_deleted_not_purged(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = Category::create(['name' => 'Makanan']);
        $menu = Menu::create([
            'code' => 'M01',
            'name' => 'Paket Dada',
            'category_id' => $category->id,
            'price' => 25000,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'transaction_code' => 'TRX2026040100001',
            'transaction_date' => '2026-04-01',
            'transaction_time' => '12:00:00',
            'total_items' => 1,
            'total_amount' => 25000,
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'menu_id' => $menu->id,
            'qty' => 1,
            'price' => 25000,
            'discount' => 0,
            'subtotal' => 25000,
        ]);

        $response = $this->actingAs($admin)->delete("/menus/{$menu->id}");
        $response->assertRedirect('/menus');

        // Verify soft deleted
        $this->assertSoftDeleted('menus', ['id' => $menu->id]);
        $this->assertDatabaseHas('transaction_items', ['menu_id' => $menu->id]);
    }
}
