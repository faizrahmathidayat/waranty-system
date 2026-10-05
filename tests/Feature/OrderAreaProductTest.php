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
use App\Models\Treatment;
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
    private Treatment $kacaFilm;
    private Treatment $antiKarat;
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
        $this->kacaFilm = Treatment::factory()->create(['code' => 'KACA_FILM', 'order_category' => 'AUTOMOTIVE']);
        $this->antiKarat = Treatment::factory()->create(['code' => 'ANTI_KARAT', 'order_category' => 'AUTOMOTIVE']);
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
            'id_treatment' => $this->kacaFilm->id_treatment,
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
            'brand' => 'LEXENT',
            'id_customer' => $this->customer->id_customer,
            'id_vehicle' => $this->vehicle->id_vehicle,
            'id_technician' => $this->technician->id_technician,
            'order_date' => now()->toDateString(),
            'discount' => 0,
            'details' => $details,
        ], $overrides);
    }

    public function test_every_automotive_area_option_is_accepted_for_any_treatment(): void
    {
        foreach (['Kaca Depan', 'Samping Belakang', 'Samping Depan', 'Belakang', 'Sunroof / Panoramic'] as $area) {
            foreach ([$this->kacaFilm, $this->antiKarat] as $treatment) {
                $this->postJson('/order/store', $this->payload([$this->item(['area' => $area, 'id_treatment' => $treatment->id_treatment])]))
                    ->assertStatus(201);
            }
        }

        $this->assertSame(10, Order::count());
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
        $treatment = Treatment::factory()->create(['code' => 'TEMBOK', 'order_category' => 'BUILDING']);
        $product = Product::factory()->create(['id_product_type' => $buildingType->id_product_type, 'status' => 'enabled', 'is_warranty_eligible' => false]);

        $this->postJson('/order/store', [
            'order_type' => 'BUILDING', 'brand' => 'LEXENT',
            'id_customer' => $this->customer->id_customer, 'id_building' => $building->id_building,
            'id_technician' => $this->technician->id_technician, 'order_date' => now()->toDateString(), 'discount' => 0,
            'details' => [[
                'id_treatment' => $treatment->id_treatment, 'id_product' => $product->id_product,
                'area' => 'Lobby Lantai 1', 'total_luas' => 12.5, 'quantity' => 1, 'unit' => 'm2', 'unit_price' => 50000,
            ]],
        ])->assertStatus(201);
    }

    public function test_all_items_of_an_order_must_use_the_same_product(): void
    {
        $response = $this->postJson('/order/store', $this->payload([
            $this->item(['area' => 'Kaca Depan']),
            $this->item(['area' => 'Belakang', 'id_product' => $this->productB->id_product]),
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors('details');
        $this->assertSame(0, Order::count());
    }

    public function test_only_treatment_area_price_and_discount_may_differ_between_items(): void
    {
        $v20 = ProductVariant::create(['id_product' => $this->productA->id_product, 'name' => '20%', 'value' => '20%', 'is_active' => true]);

        $id = $this->postJson('/order/store', $this->payload([
            $this->item(['area' => 'Kaca Depan', 'id_product_variant' => $v20->id_product_variant, 'unit_price' => 100000]),
            $this->item(['area' => 'Samping Depan', 'id_product_variant' => $v20->id_product_variant, 'unit_price' => 120000, 'discount' => 5000]),
            $this->item(['area' => 'Belakang', 'id_product_variant' => $v20->id_product_variant, 'id_treatment' => $this->antiKarat->id_treatment]),
        ]))->assertStatus(201)->json('id_order');

        $order = Order::findOrFail($id);
        $this->assertSame(3, $order->details()->count());
        $this->assertSame(1, $order->details()->distinct()->count('id_product'));
        $this->assertSame(1, $order->details()->distinct()->count('id_product_variant'));
        $this->assertSame(2, $order->details()->distinct()->count('id_treatment'));
    }

    public function test_all_items_of_an_order_must_use_the_same_variant(): void
    {
        $v20 = ProductVariant::create(['id_product' => $this->productA->id_product, 'name' => '20%', 'value' => '20%', 'is_active' => true]);
        $v40 = ProductVariant::create(['id_product' => $this->productA->id_product, 'name' => '40%', 'value' => '40%', 'is_active' => true]);

        $this->postJson('/order/store', $this->payload([
            $this->item(['area' => 'Kaca Depan', 'id_product_variant' => $v20->id_product_variant]),
            $this->item(['area' => 'Belakang', 'id_product_variant' => $v40->id_product_variant]),
        ]))->assertStatus(422)->assertJsonValidationErrors('details');

        // A variant on one item but none on another is a mismatch too.
        $this->postJson('/order/store', $this->payload([
            $this->item(['area' => 'Kaca Depan', 'id_product_variant' => $v20->id_product_variant]),
            $this->item(['area' => 'Belakang', 'id_treatment' => $this->antiKarat->id_treatment]),
        ]))->assertStatus(422)->assertJsonValidationErrors('details');

        $this->assertSame(0, Order::count());
    }

    public function test_an_area_can_only_be_used_once_per_order_even_with_a_different_treatment(): void
    {
        $response = $this->postJson('/order/store', $this->payload([
            $this->item(['area' => 'Kaca Depan']),
            $this->item(['area' => 'Kaca Depan', 'id_treatment' => $this->antiKarat->id_treatment]),
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors('details.1.area');
        $this->assertStringContainsString('Kaca Depan sudah dipakai', $response->json('errors')['details.1.area'][0]);
        $this->assertSame(0, Order::count());

        // All five different areas in one order is fine.
        $this->postJson('/order/store', $this->payload(array_map(
            fn ($area) => $this->item(['area' => $area]),
            ['Kaca Depan', 'Samping Belakang', 'Samping Depan', 'Belakang', 'Sunroof / Panoramic']
        )))->assertStatus(201);
    }

    public function test_legacy_areas_outside_the_dropdown_are_exempt_from_the_unique_rule(): void
    {
        $id = $this->postJson('/order/store', $this->payload([
            $this->item(['area' => 'Kaca Depan']),
            $this->item(['area' => 'Belakang', 'id_treatment' => $this->antiKarat->id_treatment]),
        ]))->assertStatus(201)->json('id_order');
        Order::findOrFail($id)->details()->update(['area' => 'Full Body']);

        $this->postJson("/order/update/{$id}", $this->payload([
            $this->item(['area' => 'Full Body']),
            $this->item(['area' => 'Full Body', 'id_treatment' => $this->antiKarat->id_treatment]),
        ]))->assertOk();
    }

    public function test_the_same_product_rule_applies_on_update_too(): void
    {
        $id = $this->postJson('/order/store', $this->payload([$this->item()]))->assertStatus(201)->json('id_order');

        $this->postJson("/order/update/{$id}", $this->payload([
            $this->item(),
            $this->item(['area' => 'Belakang', 'id_product' => $this->productB->id_product]),
        ]))->assertStatus(422)->assertJsonValidationErrors('details');
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

    public function test_the_order_form_script_builds_an_area_dropdown_for_automotive_and_locks_products(): void
    {
        $js = file_get_contents(public_path('js/order_function.js'));

        foreach (['Kaca Depan', 'Samping Belakang', 'Samping Depan', 'Belakang', 'Sunroof / Panoramic'] as $area) {
            $this->assertStringContainsString("'{$area}'", $js);
        }
        $this->assertStringContainsString('syncLockedProducts', $js);
        $this->assertStringContainsString('.va.locked', $js);
        $this->assertStringContainsString('syncAreaOptions', $js);
    }
}
