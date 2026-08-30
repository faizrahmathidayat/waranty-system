<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Customer;
use App\Models\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerVehicleBuildingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(Login::factory()->create());
    }

    public function test_creating_a_customer_with_vehicle_and_building_rows(): void
    {
        $response = $this->postJson('/customer/store', [
            'nama_customer' => 'Budi Santoso',
            'no_hp' => '081234567890',
            'email' => 'budi@example.com',
            'alamat' => 'Jl. Merdeka 1',
            'vehicles' => [
                ['no_polisi' => 'B 1234 CD', 'merk' => 'Toyota', 'model' => 'Avanza', 'warna' => 'Hitam', 'tahun' => 2020],
                ['no_polisi' => 'B 5678 EF', 'merk' => 'Honda', 'model' => 'Brio', 'warna' => 'Putih', 'tahun' => 2022],
            ],
            'buildings' => [
                ['nama_bangunan' => 'Ruko A', 'alamat' => 'Jl. Sudirman 10'],
            ],
        ]);

        $response->assertOk();

        $customer = Customer::where('email', 'budi@example.com')->firstOrFail();
        $this->assertSame(2, $customer->vehicles()->count());
        $this->assertSame(1, $customer->buildings()->count());
        $this->assertSame('B 1234 CD', $customer->vehicles()->orderBy('id_vehicle')->first()->no_polisi);
        $this->assertSame('active', $customer->vehicles()->first()->status);
        $this->assertSame('Ruko A', $customer->buildings()->first()->nama_bangunan);
    }

    public function test_customer_can_still_be_created_with_no_vehicle_or_building_rows(): void
    {
        $response = $this->postJson('/customer/store', [
            'nama_customer' => 'Sari',
            'no_hp' => '081200000000',
            'email' => 'sari@example.com',
            'alamat' => 'Jl. Melati 2',
        ]);

        $response->assertOk();
        $customer = Customer::where('email', 'sari@example.com')->firstOrFail();
        $this->assertSame(0, $customer->vehicles()->count());
        $this->assertSame(0, $customer->buildings()->count());
    }

    public function test_a_completely_blank_vehicle_row_is_silently_ignored(): void
    {
        $response = $this->postJson('/customer/store', [
            'nama_customer' => 'Wati',
            'no_hp' => '081200000001',
            'email' => 'wati@example.com',
            'alamat' => 'Jl. Melati 3',
            'vehicles' => [
                ['no_polisi' => '', 'merk' => '', 'model' => '', 'warna' => '', 'tahun' => ''],
            ],
        ]);

        $response->assertOk();
        $customer = Customer::where('email', 'wati@example.com')->firstOrFail();
        $this->assertSame(0, $customer->vehicles()->count());
    }

    public function test_incomplete_vehicle_row_is_rejected_and_nothing_is_saved(): void
    {
        $response = $this->postJson('/customer/store', [
            'nama_customer' => 'Dedi',
            'no_hp' => '081211112222',
            'email' => 'dedi@example.com',
            'alamat' => 'Jl. Kenanga 3',
            'vehicles' => [
                ['no_polisi' => 'B 9999 ZZ', 'merk' => '', 'model' => 'Xenia', 'warna' => 'Silver', 'tahun' => 2019],
            ],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['merk']);
        $this->assertNull(Customer::where('email', 'dedi@example.com')->first());
    }

    public function test_incomplete_building_row_is_rejected_and_nothing_is_saved(): void
    {
        $response = $this->postJson('/customer/store', [
            'nama_customer' => 'Eka',
            'no_hp' => '081211113333',
            'email' => 'eka@example.com',
            'alamat' => 'Jl. Kenanga 4',
            'buildings' => [
                ['nama_bangunan' => '', 'alamat' => 'Jl. Sudirman 11'],
            ],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['nama_bangunan']);
        $this->assertNull(Customer::where('email', 'eka@example.com')->first());
    }

    public function test_updating_a_customer_can_add_a_new_vehicle_row_without_touching_existing_data(): void
    {
        $customer = Customer::factory()->create(['status' => 'enabled']);

        $response = $this->postJson('/customer/update', [
            'id_customer' => $customer->id_customer,
            'nama_customer_detail' => $customer->nama_customer,
            'no_hp_detail' => $customer->no_hp,
            'email_detail' => $customer->email,
            'alamat_detail' => $customer->alamat,
            'status' => 'enabled',
            'vehicles' => [
                ['no_polisi' => 'D 111 AA', 'merk' => 'Suzuki', 'model' => 'Ertiga', 'warna' => 'Merah', 'tahun' => 2021],
            ],
            'buildings' => [
                ['nama_bangunan' => 'Gudang B', 'alamat' => 'Jl. Industri 5'],
            ],
        ]);

        $response->assertOk();

        $customer->refresh();
        $this->assertSame(1, $customer->vehicles()->count());
        $this->assertSame('D 111 AA', $customer->vehicles()->first()->no_polisi);
        $this->assertSame(1, $customer->buildings()->count());
        $this->assertSame('Gudang B', $customer->buildings()->first()->nama_bangunan);
    }
}
