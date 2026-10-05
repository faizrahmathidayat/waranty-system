<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Customer;
use App\Models\Login;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Technician;
use App\Models\Treatment;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderBrandInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private array $parts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(Login::factory()->create());

        $customer = Customer::factory()->create();
        $productType = ProductType::factory()->create(['order_category' => 'AUTOMOTIVE']);
        $this->parts = [
            'customer' => $customer,
            'vehicle' => Vehicle::factory()->create(['id_customer' => $customer->id_customer]),
            'technician' => Technician::factory()->create(),
            'treatment' => Treatment::factory()->create(['code' => 'COATING', 'order_category' => 'AUTOMOTIVE']),
            'product' => Product::factory()->create([
                'id_product_type' => $productType->id_product_type,
                'status' => 'enabled',
                'is_warranty_eligible' => true,
            ]),
        ];
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'order_type' => 'AUTOMOTIVE',
            'brand' => 'LEXENT',
            'id_customer' => $this->parts['customer']->id_customer,
            'id_vehicle' => $this->parts['vehicle']->id_vehicle,
            'id_technician' => $this->parts['technician']->id_technician,
            'order_date' => now()->toDateString(),
            'discount' => 0,
            'details' => [[
                'id_treatment' => $this->parts['treatment']->id_treatment,
                'id_product' => $this->parts['product']->id_product,
                'area' => 'Kaca Depan',
                'quantity' => 1,
                'unit_price' => 500000,
                'warranty_months' => 6,
            ]],
        ], $overrides);
    }

    public function test_brand_is_required_and_must_be_valid(): void
    {
        $this->postJson('/order/store', $this->payload(['brand' => '']))
            ->assertStatus(422)->assertJsonValidationErrors('brand');
        $this->postJson('/order/store', $this->payload(['brand' => 'OTHER']))
            ->assertStatus(422)->assertJsonValidationErrors('brand');
        $this->assertSame(0, Order::count());
    }

    public function test_brand_is_saved_on_create_and_update(): void
    {
        $id = $this->postJson('/order/store', $this->payload(['brand' => 'LEXENT']))
            ->assertStatus(201)->json('id_order');
        $this->assertSame('LEXENT', Order::findOrFail($id)->brand);

        $this->postJson("/order/update/{$id}", $this->payload(['brand' => 'GLOSSPRO']))->assertOk();
        $this->assertSame('GLOSSPRO', Order::findOrFail($id)->brand);
    }

    public function test_order_show_displays_brand_label(): void
    {
        $id = $this->postJson('/order/store', $this->payload(['brand' => 'GLOSSPRO']))->json('id_order');

        $this->get("/order/show/{$id}")->assertOk()->assertSee('GlossPro');
    }

    public function test_order_form_has_brand_dropdown_with_saved_value_selected(): void
    {
        $this->get('/order/create')->assertOk()->assertSee('Brand *', false)->assertSee('GlossPro')->assertSee('LEXENT');

        $id = $this->postJson('/order/store', $this->payload(['brand' => 'LEXENT']))->json('id_order');

        $this->get("/order/edit/{$id}")->assertOk()->assertSee('value="LEXENT" selected', false);
    }

    public function test_invoice_print_uses_logo_matching_order_brand(): void
    {
        foreach (['LEXENT' => ['lexent-logo.png', 'glosspro-logo.png'], 'GLOSSPRO' => ['glosspro-logo.png', 'lexent-logo.png']] as $brand => [$expected, $other]) {
            $idOrder = $this->postJson('/order/store', $this->payload(['brand' => $brand]))->json('id_order');
            $idInvoice = $this->postJson("/invoice/generate/{$idOrder}")->assertOk()->json('id_invoice');

            $this->get("/invoice/print/{$idInvoice}")
                ->assertOk()
                ->assertSee($expected, false)
                ->assertDontSee($other, false);
        }
    }

    public function test_invoice_print_has_no_logo_when_order_has_no_brand(): void
    {
        $idOrder = $this->postJson('/order/store', $this->payload())->json('id_order');
        $idInvoice = $this->postJson("/invoice/generate/{$idOrder}")->json('id_invoice');
        Order::whereKey($idOrder)->update(['brand' => null]);

        $this->get("/invoice/print/{$idInvoice}")
            ->assertOk()
            ->assertDontSee('lexent-logo.png', false)
            ->assertDontSee('glosspro-logo.png', false);
    }

    public function test_brand_is_included_in_order_invoice_and_warranty_lists(): void
    {
        $idOrder = $this->postJson('/order/store', $this->payload(['brand' => 'GLOSSPRO']))->json('id_order');
        $idInvoice = $this->postJson("/invoice/generate/{$idOrder}")->json('id_invoice');
        $this->postJson("/invoice/{$idInvoice}/payment", ['payment_date' => now()->toDateString(), 'amount' => 500000, 'payment_method' => 'CASH'])->assertOk();
        $kode = $this->postJson("/order/{$idOrder}/generate-warranty")->assertOk()->json('kode');

        $this->getJson('/order/json')->assertOk()->assertJsonPath('data.0.brand', 'GLOSSPRO');
        $this->getJson('/invoice/json')->assertOk()->assertJsonPath('data.0.brand', 'GLOSSPRO');

        $warranties = $this->getJson('/warranty/json')->assertOk()->json('data');
        $this->assertSame('GLOSSPRO', collect($warranties)->firstWhere('kode_warranty', $kode)['brand']);
    }

    public function test_logo_files_exist_for_every_brand(): void
    {
        foreach (Order::BRAND_LOGOS as $path) {
            $this->assertFileExists(public_path($path));
        }
    }
}
