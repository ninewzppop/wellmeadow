<?php

namespace Tests\Feature;

use App\Models\CentralStock;
use App\Models\Medications;
use App\Models\Pharmaceutical;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]));
    }

    public function test_stock_status_accessor_logic(): void
    {
        $normal = new CentralStock(['QtyInStock' => 100, 'ReorderLvl' => 50]);
        $low = new CentralStock(['QtyInStock' => 45, 'ReorderLvl' => 50]);
        $out = new CentralStock(['QtyInStock' => 0, 'ReorderLvl' => 10]);
        $nullQty = new CentralStock(['QtyInStock' => null, 'ReorderLvl' => 10]);
        $nullReorder = new CentralStock(['QtyInStock' => 5, 'ReorderLvl' => null]);

        $this->assertSame('normal', $normal->stock_status);
        $this->assertSame('low', $low->stock_status);
        $this->assertSame('out', $out->stock_status);
        $this->assertSame('out', $nullQty->stock_status);
        $this->assertSame('normal', $nullReorder->stock_status);
    }

    public function test_expiry_status_accessor_logic(): void
    {
        $expired = Pharmaceutical::create([
            'Drug_No' => 'DR90', 'Name' => 'Old Drug',
            'ExpiryDate' => now()->subDays(5)->toDateString(),
        ]);
        $near = Pharmaceutical::create([
            'Drug_No' => 'DR91', 'Name' => 'Soon Drug',
            'ExpiryDate' => now()->addDays(30)->toDateString(),
        ]);
        $ok = Pharmaceutical::create([
            'Drug_No' => 'DR92', 'Name' => 'Fine Drug',
            'ExpiryDate' => now()->addDays(200)->toDateString(),
        ]);
        $none = Pharmaceutical::create(['Drug_No' => 'DR93', 'Name' => 'No Date']);

        $this->assertSame('expired', $expired->expiry_status);
        $this->assertSame('near-expiry', $near->expiry_status);
        $this->assertSame('ok', $ok->expiry_status);
        $this->assertNull($none->expiry_status);
    }

    public function test_pharmacy_dashboard_shows_counts_and_filters(): void
    {
        Pharmaceutical::create(['Drug_No' => 'DR01', 'Name' => 'Normal Drug', 'QtyInStock' => 100, 'ReorderLvl' => 20]);
        Pharmaceutical::create(['Drug_No' => 'DR02', 'Name' => 'Low Drug', 'QtyInStock' => 5, 'ReorderLvl' => 20]);
        Pharmaceutical::create(['Drug_No' => 'DR03', 'Name' => 'Out Drug', 'QtyInStock' => 0, 'ReorderLvl' => 20]);
        Pharmaceutical::create(['Drug_No' => 'DR04', 'Name' => 'Expiring Drug', 'QtyInStock' => 50, 'ReorderLvl' => 20, 'ExpiryDate' => now()->addDays(10)->toDateString()]);
        Pharmaceutical::create(['Drug_No' => 'DR05', 'Name' => 'Dead Drug', 'QtyInStock' => 50, 'ReorderLvl' => 20, 'ExpiryDate' => now()->subDays(10)->toDateString()]);

        $page = $this->get('/pharmacy');
        $page->assertOk()->assertSee('Dead Drug');

        $this->get('/pharmacy?status=out')->assertOk()
            ->assertSee('Out Drug')->assertDontSee('Normal Drug');

        $this->get('/pharmacy?expiry=expired')->assertOk()
            ->assertSee('Dead Drug')->assertDontSee('Expiring Drug');

        $this->get('/pharmacy?search=Low')->assertOk()
            ->assertSee('Low Drug')->assertDontSee('Normal Drug');
    }

    public function test_stock_page_filters_by_surgical_type(): void
    {
        CentralStock::create(['Item_No' => 'IT01', 'Name' => 'Scalpel', 'ItemType' => 'surgical', 'QtyInStock' => 30, 'ReorderLvl' => 5]);
        CentralStock::create(['Item_No' => 'IT02', 'Name' => 'Bandage', 'ItemType' => 'non-surgical', 'QtyInStock' => 80, 'ReorderLvl' => 20]);

        $this->get('/stock?type=surgical')->assertOk()
            ->assertSee('Scalpel')->assertDontSee('Bandage');

        $this->get('/stock?type=non-surgical')->assertOk()
            ->assertSee('Bandage')->assertDontSee('Scalpel');
    }

    public function test_store_generates_next_id_and_logs_initial_movement(): void
    {
        CentralStock::create(['Item_No' => 'IT07', 'Name' => 'Existing', 'ItemType' => 'surgical', 'QtyInStock' => 1, 'ReorderLvl' => 1]);

        $response = $this->post('/stock', [
            'Name' => 'New Item',
            'ItemType' => 'surgical',
            'Description' => 'desc',
            'QtyInStock' => 25,
            'ReorderLvl' => 10,
            'CostPerUnit' => 2.5,
        ]);

        $response->assertRedirect('/stock');
        $this->assertDatabaseHas('CentralStock', ['Item_No' => 'IT08', 'Name' => 'New Item']);
        $this->assertDatabaseHas('StockMovement', ['Item_No' => 'IT08', 'QtyChange' => 25, 'Moved_By' => auth()->id()]);
    }

    public function test_restock_increments_qty_and_creates_movement(): void
    {
        CentralStock::create(['Item_No' => 'IT01', 'Name' => 'Gloves', 'ItemType' => 'surgical', 'QtyInStock' => 10, 'ReorderLvl' => 50]);

        $this->post('/stock/IT01/restock', ['quantity' => 40, 'note' => 'delivery'])
            ->assertRedirect();

        $this->assertDatabaseHas('CentralStock', ['Item_No' => 'IT01', 'QtyInStock' => 50]);
        $this->assertDatabaseHas('StockMovement', ['Item_No' => 'IT01', 'QtyChange' => 40]);
    }

    public function test_adjust_requires_note_and_decrements(): void
    {
        CentralStock::create(['Item_No' => 'IT01', 'Name' => 'Gloves', 'ItemType' => 'surgical', 'QtyInStock' => 10, 'ReorderLvl' => 5]);

        $this->post('/stock/IT01/adjust', ['quantity' => 3])->assertSessionHasErrors('note');

        $this->post('/stock/IT01/adjust', ['quantity' => 3, 'note' => 'damaged'])
            ->assertRedirect();

        $this->assertDatabaseHas('CentralStock', ['Item_No' => 'IT01', 'QtyInStock' => 7]);
        $this->assertDatabaseHas('StockMovement', ['Item_No' => 'IT01', 'QtyChange' => -3, 'Note' => 'damaged']);
    }

    public function test_adjust_cannot_go_below_zero(): void
    {
        CentralStock::create(['Item_No' => 'IT01', 'Name' => 'Gloves', 'ItemType' => 'surgical', 'QtyInStock' => 2, 'ReorderLvl' => 5]);

        $this->post('/stock/IT01/adjust', ['quantity' => 5, 'note' => 'damaged'])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('CentralStock', ['Item_No' => 'IT01', 'QtyInStock' => 2]);
    }

    public function test_delete_is_guarded_by_foreign_key(): void
    {
        $drug = Pharmaceutical::create(['Drug_No' => 'DR01', 'Name' => 'Used Drug', 'QtyInStock' => 5, 'ReorderLvl' => 2]);
        Medications::create([
            'Med_No' => 'M99', 'Pt_No' => null, 'Stf_No' => null, 'Drug_No' => $drug->Drug_No,
            'UnitsPerDay' => 1, 'AdminMethod' => 'Oral', 'StartDate' => '2026-01-01', 'FinishDate' => '2026-02-01',
        ]);

        $free = Pharmaceutical::create(['Drug_No' => 'DR02', 'Name' => 'Free Drug']);

        $this->delete('/pharmacy/DR02')->assertRedirect('/pharmacy');
        $this->assertDatabaseMissing('Pharmaceutical', ['Drug_No' => 'DR02']);

        $response = $this->delete('/pharmacy/DR01');
        $response->assertRedirect();
        $this->assertDatabaseHas('Pharmaceutical', ['Drug_No' => 'DR01']);
    }
}
