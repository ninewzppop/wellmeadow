<?php

namespace Tests\Feature;

use App\Models\CentralStock;
use App\Models\Pharmaceutical;
use App\Models\Stf;
use App\Models\User;
use App\Models\Wardrequisition;
use App\Models\Wd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WardRequisitionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::create(['name' => 'Test', 'email' => 'test@example.com', 'password' => 'x']));
    }

    private function fixtures(): void
    {
        Wd::create(['Wd_No' => 'WD01', 'Wd_Name' => 'Ward A']);
        Stf::create(['Stf_No' => 'S1001', 'FirstName' => 'Nurse', 'LastName' => 'A']);
        CentralStock::create(['Item_No' => 'IT01', 'Name' => 'Bandage', 'ItemType' => 'surgical', 'QtyInStock' => 10, 'ReorderLvl' => 5, 'CostPerUnit' => 10]);
        CentralStock::create(['Item_No' => 'IT02', 'Name' => 'Gauze', 'ItemType' => 'non-surgical', 'QtyInStock' => 5, 'ReorderLvl' => 3, 'CostPerUnit' => 2]);
        Pharmaceutical::create(['Drug_No' => 'DR01', 'Name' => 'Paracetamol', 'QtyInStock' => 10, 'ReorderLvl' => 5, 'CostPerUnit' => 5]);
    }

    public function test_create_generates_w_r1_and_stores_items(): void
    {
        $this->fixtures();

        $res = $this->post(route('requisitions.store'), [
            'Wd_No' => 'WD01', 'Stf_No' => 'S1001',
            'items' => [
                ['ref' => 'ITEM:IT01', 'QtyReq' => 2],
                ['ref' => 'DRUG:DR01', 'QtyReq' => 3],
            ],
        ]);

        $res->assertRedirect(route('requisitions.show', 'WR1'));
        $this->assertDatabaseHas('Wardrequisitions', ['Wd_Req_No' => 'WR1', 'status' => 'Pending']);
        $this->assertDatabaseHas('Itemrequest', ['Wd_Req_No' => 'WR1', 'Item_No' => 'IT01', 'QtyReq' => 2]);
        $this->assertDatabaseHas('Drugrequest', ['Wd_Req_No' => 'WR1', 'Drug_No' => 'DR01', 'QtyReq' => 3]);
    }

    public function test_approve_deducts_stock_and_creates_movements(): void
    {
        $this->fixtures();
        $this->post(route('requisitions.store'), [
            'Wd_No' => 'WD01', 'Stf_No' => 'S1001',
            'items' => [['ref' => 'ITEM:IT01', 'QtyReq' => 3]],
        ]);

        $req = Wardrequisition::first();
        $this->post(route('requisitions.approve', $req))->assertRedirect();

        $this->assertDatabaseHas('CentralStock', ['Item_No' => 'IT01', 'QtyInStock' => 7]);
        $this->assertDatabaseHas('StockMovement', ['Item_No' => 'IT01', 'QtyChange' => -3]);
        $this->assertDatabaseHas('Wardrequisitions', ['Wd_Req_No' => 'WR1', 'status' => 'Approved']);
    }

    public function test_out_of_stock_blocks_approve(): void
    {
        $this->fixtures();
        $this->post(route('requisitions.store'), [
            'Wd_No' => 'WD01', 'Stf_No' => 'S1001',
            'items' => [['ref' => 'ITEM:IT01', 'QtyReq' => 100]],
        ]);
        $req = Wardrequisition::first();
        $res = $this->post(route('requisitions.approve', $req));
        $res->assertSessionHasErrors('queue');
        $this->assertDatabaseHas('Wardrequisitions', ['Wd_Req_No' => 'WR1', 'status' => 'Pending']);
        $this->assertDatabaseHas('CentralStock', ['Item_No' => 'IT01', 'QtyInStock' => 10]);
    }

    public function test_receive_moves_to_history(): void
    {
        $this->fixtures();
        $this->post(route('requisitions.store'), [
            'Wd_No' => 'WD01', 'Stf_No' => 'S1001',
            'items' => [['ref' => 'DRUG:DR01', 'QtyReq' => 1]],
        ]);
        $req = Wardrequisition::first();
        $this->post(route('requisitions.approve', $req));
        $this->post(route('requisitions.receive', $req), ['Received_By' => 'S1001', 'DateRecv' => now()->toDateString()])->assertRedirect();
        $this->assertDatabaseHas('Wardrequisitions', ['Wd_Req_No' => 'WR1', 'status' => 'Completed']);
        // queue hides completed, history shows it
        $this->get(route('requisitions.index'))->assertSee(__('No pending requisitions.'));
        $this->get(route('requisitions.history'))->assertSee('WR1');
    }
}
