<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Building;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Login;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductVariant;
use App\Models\Technician;
use App\Models\Vehicle;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalWarrantyPageTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;
    private Technician $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(Login::factory()->create());

        $this->customer = Customer::factory()->create(['nama_customer' => 'Budi Santoso', 'no_hp' => '081234567890', 'alamat' => 'Jl. Melati No. 10']);
        $this->technician = Technician::factory()->create();
    }

    /** Runs the real order -> invoice -> payment -> warranty flow and returns the warranty code. */
    private function warranty(array $orderFields, array $details, int $warrantyMonths = 6): string
    {
        $response = $this->postJson('/order/store', array_merge([
            'brand' => 'LEXENT',
            'id_customer' => $this->customer->id_customer,
            'id_technician' => $this->technician->id_technician,
            'order_date' => now()->toDateString(),
            'discount' => 0,
            'details' => array_map(fn ($d) => $d + ['quantity' => 1, 'unit_price' => 500000, 'warranty_months' => $warrantyMonths], $details),
        ], $orderFields));
        $this->assertSame(201, $response->status(), $response->getContent());
        $idOrder = $response->json('id_order');

        $idInvoice = $this->postJson("/invoice/generate/{$idOrder}")->assertOk()->json('id_invoice');
        $this->postJson("/invoice/{$idInvoice}/payment", ['payment_date' => now()->toDateString(), 'amount' => Invoice::findOrFail($idInvoice)->grand_total, 'payment_method' => 'CASH'])->assertOk();

        return $this->postJson("/order/{$idOrder}/generate-warranty")->assertOk()->json('kode');
    }

    /** @param string[] $areas */
    private function automotiveWarranty(array $areas, bool $guest = true): string
    {
        $vehicle = Vehicle::factory()->create(['id_customer' => $this->customer->id_customer, 'no_polisi' => 'B 1234 ABC', 'merk' => 'Toyota', 'model' => 'Fortuner']);
        $type = ProductType::factory()->create(['name' => 'Kaca Film', 'order_category' => 'AUTOMOTIVE']);
        $product = Product::factory()->create(['nama_produk' => 'LEXENT HT', 'id_product_type' => $type->id_product_type, 'status' => 'enabled', 'is_warranty_eligible' => true]);

        $kode = $this->warranty(
            ['order_type' => 'AUTOMOTIVE', 'id_vehicle' => $vehicle->id_vehicle, 'subtotal' => 500000],
            array_map(fn ($area) => ['id_product_type' => $type->id_product_type, 'id_product' => $product->id_product, 'area' => $area], $areas)
        );

        if ($guest) auth()->logout(); // the public page is for customers: no login

        return $kode;
    }

    private function buildingWarranty(): string
    {
        $building = Building::factory()->create(['id_customer' => $this->customer->id_customer, 'nama_bangunan' => 'Gedung Mawar', 'alamat' => 'Jl. Anggrek 5']);
        $type = ProductType::factory()->create(['name' => 'Kaca Film Gedung', 'order_category' => 'BUILDING']);
        $product = Product::factory()->create(['nama_produk' => 'High Perform', 'id_product_type' => $type->id_product_type, 'status' => 'enabled', 'is_warranty_eligible' => true]);
        $variant = ProductVariant::create(['id_product' => $product->id_product, 'name' => 'HT 15', 'value' => '15%', 'is_active' => true]);

        $item = fn ($area, $luas) => ['id_product' => $product->id_product, 'id_product_variant' => $variant->id_product_variant, 'area' => $area, 'total_luas' => $luas, 'unit' => 'm2'];

        $kode = $this->warranty(['order_type' => 'BUILDING', 'id_building' => $building->id_building], [$item('Lt 1', 11), $item('Lt 2', 12.5)]);
        auth()->logout();

        return $kode;
    }

    public function test_public_page_shows_the_warranty_details_without_login(): void
    {
        $kode = $this->automotiveWarranty(['Kaca Depan']);

        $this->get('/warranty/' . $kode)
            ->assertOk()
            ->assertSee('E-WARRANTY CARD')
            ->assertSee($kode)
            ->assertSee('Budi Santoso')
            ->assertSee('081234567890')
            ->assertSee('Jl. Melati No. 10')
            ->assertDontSee('Informasi Garansi')->assertDontSee('Masa Garansi')
            ->assertSee('WARRANTY ACTIVE')
            ->assertSee('images/warranty-cover.jpg', false);
    }

    public function test_logo_follows_the_order_brand(): void
    {
        $lexent = $this->automotiveWarranty(['Kaca Depan']);
        $this->get('/warranty/' . $lexent)->assertOk()
            ->assertSee('images/lexent-logo.png', false)->assertDontSee('images/glosspro-logo.png', false)
            ->assertSee('images/warranty-cover.jpg', false)->assertDontSee('warranty-cover-glosspro.jpg', false);

        Warranty::where('kode_warranty', $lexent)->firstOrFail()->order()->update(['brand' => 'GLOSSPRO']);
        $this->get('/warranty/' . $lexent)->assertOk()
            ->assertSee('images/glosspro-logo.png', false)->assertDontSee('images/lexent-logo.png', false)
            ->assertSee('images/warranty-cover-glosspro.jpg', false)
            ->assertSee('Official E-Warranty GlossPro');
    }

    public function test_glosspro_card_has_its_own_product_list_contact_and_wording(): void
    {
        $kode = $this->automotiveWarranty(['Kaca Depan']);
        $this->get('/warranty/' . $kode)->assertOk()
            ->assertSee('semua produk Lexent', false)->assertSee('Kaca Film Bangunan')->assertDontSee('Anti Karat');

        Warranty::where('kode_warranty', $kode)->firstOrFail()->order()->update(['brand' => 'GLOSSPRO']);
        $this->get('/warranty/' . $kode)->assertOk()
            ->assertSee('semua produk GlossPro', false)
            ->assertSeeInOrder(['Kaca Film Mobil', 'Coating', 'PPF', 'Anti Karat'])
            ->assertDontSee('Kaca Film Bangunan')->assertDontSee('Lexent')
            ->assertSee('0858 8889 9558')->assertDontSee('0815 1345 5525')->assertSee('www.glossproindonesia.com')->assertDontSee('lexentindonesia');
    }

    public function test_warranty_code_prefix_follows_the_order_brand(): void
    {
        $vehicle = Vehicle::factory()->create(['id_customer' => $this->customer->id_customer]);
        $type = ProductType::factory()->create(['order_category' => 'AUTOMOTIVE']);
        $product = Product::factory()->create(['id_product_type' => $type->id_product_type, 'status' => 'enabled', 'is_warranty_eligible' => true]);
        $details = [['id_product_type' => $type->id_product_type, 'id_product' => $product->id_product, 'area' => 'Kaca Depan']];

        $kode = $this->warranty(['order_type' => 'AUTOMOTIVE', 'brand' => 'GLOSSPRO', 'id_vehicle' => $vehicle->id_vehicle, 'subtotal' => 500000], $details);

        $this->assertSame('GP-' . now()->format('Y') . '-00001', $kode);
    }

    public function test_product_status_is_inactive_once_the_item_has_expired(): void
    {
        $kode = $this->automotiveWarranty(['Kaca Depan']);
        $warranty = Warranty::where('kode_warranty', $kode)->firstOrFail();
        $warranty->warrantyItems()->update(['tanggal_expired' => now()->subDay()->toDateString()]);

        $html = $this->get('/warranty/' . $kode)->assertOk()->getContent();
        preg_match('/<h2>Informasi Produk<\/h2>(.*?)<div class="sec-wrap">/s', $html, $m);

        $this->assertStringContainsString('Inactive', $m[1]);
        $this->assertStringNotContainsString('>Active<', $m[1]);
    }

    public function test_automotive_lists_one_table_row_per_product_with_jenis_tipe_and_area(): void
    {
        $kode = $this->automotiveWarranty(['Kaca Depan']);

        $html = $this->get('/warranty/' . $kode)->assertOk()->getContent();
        preg_match('/<h2>Informasi Produk<\/h2>(.*?)<div class="sec-wrap">/s', $html, $m);
        $section = preg_replace('/\s+/', ' ', strip_tags(str_replace(['</th>', '</td>'], ['</th> ', '</td> '], $m[1])));

        $this->assertStringContainsString('<table class="ptable">', $m[1]);
        $this->assertStringContainsString('Jenis Produk Tipe Area Berlaku Mulai Berlaku Sampai Status Kaca Film LEXENT HT Kaca Depan '.now()->format('d / m / Y').' '.now()->addMonths(6)->format('d / m / Y').' Active', $section);
        foreach (['Samping Depan', 'Samping Belakang', 'Belakang', 'Sunroof'] as $other) {
            $this->assertStringNotContainsString($other, $section);
        }
    }

    public function test_automotive_areas_are_listed_in_the_cards_own_order(): void
    {
        $kode = $this->automotiveWarranty(['Belakang', 'Samping Belakang', 'Kaca Depan', 'Sunroof / Panoramic', 'Samping Depan']);

        $this->get('/warranty/' . $kode)->assertOk()
            ->assertSeeInOrder(['Jenis Produk', 'Kaca Depan', 'Samping Depan', 'Samping Belakang', 'Belakang', 'Sunroof / Panoramic']);
        $this->assertSame(5, substr_count($this->get('/warranty/' . $kode)->getContent(), '<td>LEXENT HT</td>'));
    }

    public function test_vehicle_and_plate_are_in_the_customer_section_and_the_old_product_rows_are_gone(): void
    {
        $kode = $this->automotiveWarranty(['Kaca Depan']);

        $this->get('/warranty/' . $kode)->assertOk()
            ->assertSeeInOrder(['Data Customer', 'Kendaraan', 'Toyota Fortuner', 'No. Plat Kendaraan', 'B 1234 ABC', 'Informasi Produk'])
            ->assertDontSee('Nama Produk / Series')
            ->assertDontSee('Warna / Shade')
            ->assertDontSee('Detail Item')
            ->assertDontSee('Teknisi')
            ->assertDontSee('No. Invoice');
    }

    public function test_building_shows_one_block_per_item_with_lokasi_jenis_produk_tipe_and_luas(): void
    {
        $kode = $this->buildingWarranty();

        $html = $this->get('/warranty/' . $kode)->assertOk()
            ->assertSeeInOrder(['Data Customer', 'Properti', 'Gedung Mawar', 'Alamat Properti', 'Jl. Anggrek 5', 'Informasi Produk',
                'Lokasi', 'Lt 1', 'Jenis Produk', 'High Perform', 'Tipe', 'HT 15', 'Luas', '11 m²', 'Berlaku Mulai', 'Berlaku Sampai',
                'Lokasi', 'Lt 2', 'Jenis Produk', 'High Perform', 'Tipe', 'HT 15', 'Luas', '12,5 m²', 'Berlaku Mulai', 'Berlaku Sampai', 'Jenis Produk Lainnya'])
            ->getContent();

        $this->assertSame(2, substr_count($html, 'class="rows block"'));
        $this->assertStringNotContainsString('Kendaraan', $html);
    }

    public function test_building_tipe_is_a_dash_when_the_order_has_no_variant(): void
    {
        $building = Building::factory()->create(['id_customer' => $this->customer->id_customer]);
        $type = ProductType::factory()->create(['order_category' => 'BUILDING']);
        $product = Product::factory()->create(['nama_produk' => 'Tanpa Varian', 'id_product_type' => $type->id_product_type, 'status' => 'enabled', 'is_warranty_eligible' => true]);

        $kode = $this->warranty(['order_type' => 'BUILDING', 'id_building' => $building->id_building], [
            ['id_product' => $product->id_product, 'area' => 'Lt 1', 'total_luas' => 8, 'unit' => 'm2'],
        ]);
        auth()->logout();

        $html = $this->get('/warranty/' . $kode)->assertOk()
            ->assertSeeInOrder(['Lokasi', 'Lt 1', 'Jenis Produk', 'Tanpa Varian', 'Tipe', '-', 'Luas', '8 m²'])
            ->getContent();

        $this->assertMatchesRegularExpression('/<dt>Tipe<\/dt><dd>-<\/dd>/', $html);
    }

    public function test_page_uses_the_cover_artwork_and_no_qr_code(): void
    {
        $html = $this->get('/warranty/' . $this->automotiveWarranty(['Kaca Depan']))->assertOk()->getContent();

        $this->assertStringNotContainsStringIgnoringCase('qrcode', $html);
        $this->assertStringNotContainsString('Scan QR', $html);
        $this->assertFileExists(public_path('images/warranty-cover.jpg'));
        $this->assertFileExists(public_path('images/lexent-logo.png'));
    }

    public function test_customer_service_contact_comes_from_config(): void
    {
        $kode = $this->automotiveWarranty(['Kaca Depan']);

        $this->get('/warranty/' . $kode)->assertSee('0858 8889 9558')->assertSee('www.lexentindonesia.com');

        config(['warranty_card.customer_service_whatsapp' => '0811 000 111', 'warranty_card.website' => 'www.contoh.test']);

        $this->get('/warranty/' . $kode)->assertSee('0811 000 111')->assertSee('www.contoh.test')->assertDontSee('0858 8889 9558');
    }

    public function test_cover_label_is_inactive_only_when_every_product_is_inactive(): void
    {
        $kode = $this->automotiveWarranty(['Kaca Depan', 'Belakang']);
        $warranty = Warranty::where('kode_warranty', $kode)->firstOrFail();
        $expired = now()->subDay()->toDateString();

        // One product expired, one still running: the card stays active.
        $warranty->warrantyItems()->orderBy('id_warranty_item')->first()->update(['tanggal_expired' => $expired]);
        $this->get('/warranty/' . $kode)->assertOk()->assertSee('WARRANTY ACTIVE')->assertDontSee('WARRANTY INACTIVE');

        // Every product expired: the card is inactive.
        $warranty->warrantyItems()->update(['tanggal_expired' => $expired]);
        $this->get('/warranty/' . $kode)->assertOk()->assertSee('WARRANTY INACTIVE')->assertDontSee('WARRANTY ACTIVE');
    }

    public function test_void_or_unknown_warranty_is_not_found(): void
    {
        $kode = $this->automotiveWarranty(['Kaca Depan']);

        $this->get('/warranty/WR9999999')->assertNotFound();

        Warranty::where('kode_warranty', $kode)->update(['status' => 'Void']);
        $this->get('/warranty/' . $kode)->assertNotFound();
    }

    public function test_pin_is_not_required(): void
    {
        $kode = $this->automotiveWarranty(['Kaca Depan']);

        $this->get('/warranty/' . $kode)->assertDontSee('PIN')->assertDontSee('Verifikasi');
    }
}
