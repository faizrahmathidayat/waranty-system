<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\Login;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Technician;
use App\Models\Treatment;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(Login::factory()->create());
    }

    private function makeInvoice(): int
    {
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['id_customer' => $customer->id_customer]);
        $technician = Technician::factory()->create();
        $productType = ProductType::factory()->create(['order_category' => 'AUTOMOTIVE']);
        $treatment = Treatment::factory()->create(['code' => 'COATING', 'order_category' => 'AUTOMOTIVE']);
        $product = Product::factory()->create([
            'id_product_type' => $productType->id_product_type,
            'status' => 'enabled',
            'is_warranty_eligible' => true,
        ]);

        $idOrder = $this->postJson('/order/store', [
            'order_type' => 'AUTOMOTIVE',
            'brand' => 'GLOSSPRO',
            'id_customer' => $customer->id_customer,
            'id_vehicle' => $vehicle->id_vehicle,
            'id_technician' => $technician->id_technician,
            'order_date' => now()->toDateString(),
            'discount' => 0,
            'details' => [[
                'id_treatment' => $treatment->id_treatment,
                'id_product' => $product->id_product,
                'area' => 'Kaca Depan',
                'quantity' => 1,
                'unit_price' => 500000,
                'warranty_months' => 6,
            ]],
        ])->assertStatus(201)->json('id_order');

        return $this->postJson("/invoice/generate/{$idOrder}")->assertStatus(200)->json('id_invoice');
    }

    public function test_print_lists_active_bank_accounts_only(): void
    {
        BankAccount::factory()->create(['bank_name' => 'BCA', 'account_number' => '1234567890', 'account_holder' => 'PT Contoh Jaya']);
        BankAccount::factory()->create(['bank_name' => 'Mandiri', 'account_number' => '9876543210', 'account_holder' => 'PT Contoh Jaya']);
        BankAccount::factory()->create(['bank_name' => 'BNI', 'account_number' => '5555555555', 'is_active' => false]);

        $this->get('/invoice/print/'.$this->makeInvoice())
            ->assertOk()
            ->assertSee('Pembayaran melalui transfer')
            ->assertSee('BCA')
            ->assertSee('1234567890')
            ->assertSee('Mandiri')
            ->assertSee('a.n. PT Contoh Jaya', false)
            ->assertDontSee('5555555555');
    }

    public function test_print_hides_bank_block_without_active_accounts(): void
    {
        BankAccount::factory()->create(['is_active' => false]);

        $this->get('/invoice/print/'.$this->makeInvoice())
            ->assertOk()
            ->assertDontSee('Pembayaran melalui transfer');
    }

    public function test_bank_account_crud_via_master_data_endpoints(): void
    {
        $this->get('/bank-account')->assertOk();

        $this->postJson('/bank-account/store', ['bank_name' => 'BCA', 'account_number' => '111', 'account_holder' => 'PT A', 'is_active' => 1])
            ->assertOk()->assertJson(['success' => true]);
        $account = BankAccount::firstOrFail();
        $this->assertTrue($account->is_active);

        $this->postJson('/bank-account/update', ['id_bank_account' => $account->id_bank_account, 'bank_name' => 'BCA', 'account_number' => '222', 'account_holder' => 'PT A', 'is_active' => 1])
            ->assertOk();
        $this->assertSame('222', $account->fresh()->account_number);

        $this->postJson('/bank-account/store', ['bank_name' => '', 'account_number' => '', 'account_holder' => ''])
            ->assertStatus(422);

        $this->postJson('/bank-account/destroy', ['id_bank_account' => $account->id_bank_account])->assertOk();
        $this->assertFalse($account->fresh()->is_active);
    }
}
