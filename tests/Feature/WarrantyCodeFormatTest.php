<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Customer;
use App\Models\Login;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Technician;
use App\Models\Treatment;
use App\Models\Vehicle;
use App\Services\WarrantyCodeGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WarrantyCodeFormatTest extends TestCase
{
    use RefreshDatabase;

    private function insertCode(string $code): void
    {
        DB::table('warranties')->insert(['kode_warranty' => $code, 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function generator(): WarrantyCodeGenerator
    {
        return new WarrantyCodeGenerator();
    }

    public function test_first_code_of_the_year_is_00001(): void
    {
        $this->assertSame('LEX-2026-00001', $this->generator()->next(Carbon::create(2026, 10, 6)));
    }

    public function test_the_sequence_continues_numerically_within_the_year(): void
    {
        $this->insertCode('LEX-2026-00001');
        $this->insertCode('LEX-2026-00002');

        $this->assertSame('LEX-2026-00003', $this->generator()->next(Carbon::create(2026, 1, 1)));
    }

    public function test_the_sequence_restarts_every_year_and_other_years_are_untouched(): void
    {
        $this->insertCode('LEX-2025-00007');

        $this->assertSame('LEX-2026-00001', $this->generator()->next(Carbon::create(2026, 1, 1)));
        $this->assertSame('LEX-2025-00008', $this->generator()->next(Carbon::create(2025, 12, 31)));
    }

    public function test_old_wr_codes_are_ignored_and_stay_valid(): void
    {
        $this->insertCode('WR2026004');
        $this->insertCode('WR2026052');

        $this->assertSame('LEX-2026-00001', $this->generator()->next(Carbon::create(2026, 5, 5)));
        $this->assertSame(1, DB::table('warranties')->where('kode_warranty', 'WR2026004')->count());
    }

    public function test_past_99999_the_number_just_gets_longer_and_is_still_found_numerically(): void
    {
        $this->insertCode('LEX-2026-99999');
        $this->assertSame('LEX-2026-100000', $this->generator()->next(Carbon::create(2026, 12, 31)));

        // "100000" sorts before "99999" as a string; the next number must still be 100001.
        $this->insertCode('LEX-2026-100000');
        $this->assertSame('LEX-2026-100001', $this->generator()->next(Carbon::create(2026, 12, 31)));
    }

    public function test_the_code_fits_the_column_and_is_unique(): void
    {
        $this->insertCode('LEX-2026-00001');

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->insertCode('LEX-2026-00001');
    }

    public function test_warranties_created_from_orders_get_sequential_lex_codes_and_open_publicly(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(Login::factory()->create());

        $customer = Customer::factory()->create();
        $type = ProductType::factory()->create(['order_category' => 'AUTOMOTIVE']);
        $treatment = Treatment::factory()->create(['code' => 'COATING', 'order_category' => 'AUTOMOTIVE']);
        $product = Product::factory()->create(['id_product_type' => $type->id_product_type, 'status' => 'enabled', 'is_warranty_eligible' => true]);

        $codes = [];
        foreach (['Kaca Depan', 'Belakang'] as $area) {
            $vehicle = Vehicle::factory()->create(['id_customer' => $customer->id_customer]);
            $idOrder = $this->postJson('/order/store', [
                'order_type' => 'AUTOMOTIVE', 'brand' => 'LEXENT', 'id_customer' => $customer->id_customer, 'id_vehicle' => $vehicle->id_vehicle,
                'id_technician' => Technician::factory()->create()->id_technician, 'order_date' => now()->toDateString(), 'discount' => 0,
                'details' => [['id_treatment' => $treatment->id_treatment, 'id_product' => $product->id_product, 'area' => $area, 'quantity' => 1, 'unit_price' => 500000, 'warranty_months' => 6]],
            ])->assertStatus(201)->json('id_order');
            $idInvoice = $this->postJson("/invoice/generate/{$idOrder}")->assertOk()->json('id_invoice');
            $this->postJson("/invoice/{$idInvoice}/payment", ['payment_date' => now()->toDateString(), 'amount' => 500000, 'payment_method' => 'CASH'])->assertOk();
            $codes[] = $this->postJson("/order/{$idOrder}/generate-warranty")->assertOk()->json('kode');
        }

        $year = now()->format('Y');
        $this->assertSame(["LEX-{$year}-00001", "LEX-{$year}-00002"], $codes);

        auth()->logout();
        $this->get('/warranty/' . $codes[0])->assertOk()->assertSee("LEX-{$year}-00001");
        $this->getJson('/warranty/' . strtolower($codes[0]) . '/check')->assertOk()->assertJson(['valid' => true]);
    }

    public function test_manually_created_warranties_use_the_same_format(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(Login::factory()->create());
        $this->insertCode('LEX-' . now()->format('Y') . '-00041');

        $generated = $this->invokeGenerateKode();

        $this->assertSame('LEX-' . now()->format('Y') . '-00042', $generated);
    }

    private function invokeGenerateKode(): string
    {
        $controller = app(\App\Http\Controllers\WarrantyController::class);
        $method = new \ReflectionMethod($controller, 'generateKodeWarranty');
        $method->setAccessible(true);

        return DB::transaction(fn () => $method->invoke($controller));
    }
}
