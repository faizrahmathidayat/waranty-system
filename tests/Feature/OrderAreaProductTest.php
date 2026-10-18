<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Building;
use App\Models\Customer;
use App\Models\Login;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductVariant;
use App\Models\Technician;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAreaProductTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;
    private Vehicle $vehicle;
    private Technician $technician;
    private ProductType $autoType;
    private Product $productA;
    private Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(Login::factory()->create());

        $this->customer = Customer::factory()->create();
        $this->vehicle = Vehicle::factory()->create(['id_customer' => $this->customer->id_customer]);
        $this->technician = Technician::factory()->create();
        $this->autoType = ProductType::factory()->create(['order_category' => 'AUTOMOTIVE']);
        $this->productA = $this->product(['nama_produk' => 'Produk A']);
        $this->productB = $this->product(['nama_produk' => 'Produk B']);
    }

    private function product(array $attributes = []): Product
    {
        return Product::factory()->create($attributes + ['id_product_type' => $this->autoType->id_product_type, 'status' => 'enabled', 'is_warranty_eligible' => false]);
    }

    private function item(array $overrides = []): array
    {
        return array_merge([
            'id_product' => $this->productA->id_product,
            'area' => 'Kaca Depan',
            'quantity' => 1,
            'unit_price' => 100000,
        ], $overrides);
    }

    private function payload(array $details, array $overrides = []): array
    {
        return array_merge([
            'order_type' => 'AUTOMOTIVE',
            'id_customer' => $this->customer->id_customer,
            'id_vehicle' => $this->vehicle->id_vehicle,
            'id_technician' => $this->technician->id_technician,
            'order_date' => now()->toDateString(),
            'discount' => 0,
            'details' => $details,
        ], $overrides);
    }

    public function test_every_automotive_area_option_is_accepted(): void
    {
        foreach (['Kaca Depan', 'Samping Belakang', 'Samping Depan', 'Belakang', 'Sunroof / Panoramic'] as $area) {
            $this->postJson('/order/store', $this->payload([$this->item(['area' => $area])]))
                ->assertStatus(201);
        }

        $this->assertSame(5, Order::count());
    }

    public function test_order_items_are_saved_without_a_treatment(): void
    {
        $id = $this->postJson('/order/store', $this->payload([$this->item()]))->assertStatus(201)->json('id_order');

        $detail = Order::findOrFail($id)->details()->firstOrFail();
        $this->assertNull($detail->id_treatment);
        $this->assertNull($detail->treatment_name_snapshot);
    }

    public function test_product_list_for_the_order_form_needs_no_treatment(): void
    {
        $this->getJson('/order/products?order_type=AUTOMOTIVE')->assertOk()
            ->assertJsonCount(2, 'data');
        $this->get('/treatment')->assertNotFound();
    }

    public function test_brand_is_no_longer_part_of_the_order_form_or_lists(): void
    {
        $this->get('/order/create')->assertOk()->assertDontSee('Pilih Brand', false)->assertDontSee('name="brand"', false);
        $this->get('/product')->assertOk()->assertDontSee('name="brand"', false)->assertDontSee('<th>Brand</th>', false);

        $id = $this->postJson('/order/store', $this->payload([$this->item()]))->assertStatus(201)->json('id_order');
        $this->assertNull(Order::findOrFail($id)->brand);
        $this->assertSame('Produk A', Order::findOrFail($id)->details()->firstOrFail()->product_name_snapshot);
    }

    public function test_automotive_area_must_be_one_of_the_options(): void
    {
        foreach (['Full Body', 'Samping Kanan Depan', 'Lainnya', '', 'Hood'] as $area) {
            $response = $this->postJson('/order/store', $this->payload([$this->item(['area' => $area])]));
            $response->assertStatus(422)->assertJsonValidationErrors('details.0.area');
        }

        $this->assertSame(0, Order::count());
    }

    public function test_building_area_stays_free_text(): void
    {
        $building = Building::factory()->create(['id_customer' => $this->customer->id_customer]);
        $buildingType = ProductType::factory()->create(['order_category' => 'BUILDING']);
        $product = Product::factory()->create(['id_product_type' => $buildingType->id_product_type, 'status' => 'enabled', 'is_warranty_eligible' => false]);

        $this->postJson('/order/store', [
            'order_type' => 'BUILDING',
            'id_customer' => $this->customer->id_customer, 'id_building' => $building->id_building,
            'id_technician' => $this->technician->id_technician, 'order_date' => now()->toDateString(), 'discount' => 0,
            'details' => [[
                'id_product' => $product->id_product,
                'area' => 'Lobby Lantai 1', 'total_luas' => 12.5, 'quantity' => 1, 'unit' => 'm2', 'unit_price' => 50000,
            ]],
        ])->assertStatus(201);
    }

    public function test_items_of_one_order_may_use_different_products_and_variants(): void
    {
        $v20 = ProductVariant::create(['id_product' => $this->productA->id_product, 'name' => '20%', 'value' => '20%', 'is_active' => true]);
        $v40 = ProductVariant::create(['id_product' => $this->productA->id_product, 'name' => '40%', 'value' => '40%', 'is_active' => true]);

        $id = $this->postJson('/order/store', $this->payload([
            $this->item(['area' => 'Kaca Depan', 'id_product_variant' => $v20->id_product_variant]),
            $this->item(['area' => 'Belakang', 'id_product_variant' => $v40->id_product_variant, 'unit_price' => 120000, 'discount' => 5000]),
            $this->item(['area' => 'Samping Depan', 'id_product' => $this->productB->id_product]),
        ]))->assertStatus(201)->json('id_order');

        $order = Order::findOrFail($id);
        $this->assertSame(3, $order->details()->count());
        $this->assertSame(2, $order->details()->distinct()->count('id_product'));
        $this->assertSame(2, $order->details()->distinct()->count('id_product_variant'));
    }

    public function test_the_same_area_can_be_used_again_with_a_different_product_or_variant(): void
    {
        $v20 = ProductVariant::create(['id_product' => $this->productA->id_product, 'name' => '20%', 'value' => '20%', 'is_active' => true]);

        $this->postJson('/order/store', $this->payload([
            $this->item(['area' => 'Kaca Depan']),
            $this->item(['area' => 'Kaca Depan', 'id_product' => $this->productB->id_product]),
            $this->item(['area' => 'Kaca Depan', 'id_product_variant' => $v20->id_product_variant]),
        ]))->assertStatus(201);
    }

    public function test_the_same_area_product_and_variant_cannot_be_repeated_in_an_order(): void
    {
        $response = $this->postJson('/order/store', $this->payload([
            $this->item(['area' => 'Kaca Depan']),
            $this->item(['area' => 'Kaca Depan']),
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors('details.1');
        $this->assertSame(0, Order::count());

        $v20 = ProductVariant::create(['id_product' => $this->productA->id_product, 'name' => '20%', 'value' => '20%', 'is_active' => true]);
        $this->postJson('/order/store', $this->payload([
            $this->item(['area' => 'Kaca Depan', 'id_product_variant' => $v20->id_product_variant]),
            $this->item(['area' => 'Kaca Depan', 'id_product_variant' => $v20->id_product_variant]),
        ]))->assertStatus(422)->assertJsonValidationErrors('details.1');
        $this->assertSame(0, Order::count());
    }

    public function test_the_unique_rule_applies_on_update_too(): void
    {
        $id = $this->postJson('/order/store', $this->payload([$this->item()]))->assertStatus(201)->json('id_order');

        $this->postJson("/order/update/{$id}", $this->payload([
            $this->item(),
            $this->item(['id_product' => $this->productB->id_product]),
        ]))->assertOk();

        $this->postJson("/order/update/{$id}", $this->payload([$this->item(), $this->item()]))
            ->assertStatus(422)->assertJsonValidationErrors('details.1');
    }

    public function test_several_legacy_areas_outside_the_dropdown_can_be_saved_again(): void
    {
        $id = $this->postJson('/order/store', $this->payload([
            $this->item(['area' => 'Kaca Depan']),
            $this->item(['area' => 'Belakang']),
        ]))->assertStatus(201)->json('id_order');
        $details = Order::findOrFail($id)->details()->orderBy('id_order_detail')->get();
        $details[0]->update(['area' => 'Full Body']);
        $details[1]->update(['area' => 'Hood']);

        $this->postJson("/order/update/{$id}", $this->payload([
            $this->item(['area' => 'Full Body']),
            $this->item(['area' => 'Hood']),
        ]))->assertOk();
    }

    public function test_an_older_order_keeps_its_old_area_when_edited_but_cannot_gain_new_invalid_ones(): void
    {
        $id = $this->postJson('/order/store', $this->payload([$this->item()]))->assertStatus(201)->json('id_order');
        // Simulate an order created before the area dropdown existed.
        Order::findOrFail($id)->details()->update(['area' => 'Full Body']);

        $this->postJson("/order/update/{$id}", $this->payload([$this->item(['area' => 'Full Body'])]))->assertOk();
        $this->postJson("/order/update/{$id}", $this->payload([$this->item(['area' => 'Full Body']), $this->item(['area' => 'Hood'])]))
            ->assertStatus(422)->assertJsonValidationErrors('details.1.area');
    }

    public function test_the_order_form_script_builds_an_area_dropdown_for_automotive(): void
    {
        $js = file_get_contents(public_path('js/order_function.js'));

        foreach (['Kaca Depan', 'Samping Belakang', 'Samping Depan', 'Belakang', 'Sunroof / Panoramic'] as $area) {
            $this->assertStringContainsString("'{$area}'", $js);
        }
        $this->assertStringNotContainsString('syncLockedProducts', $js);
    }
}
