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
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalWarrantyPageTest extends TestCase
{
    use RefreshDatabase;

    private string $kode;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(Login::factory()->create());

        $this->customer = Customer::factory()->create(['nama_customer' => 'Budi Santoso', 'no_hp' => '081234567890', 'alamat' => 'Jl. Melati No. 10']);
        $vehicle = Vehicle::factory()->create(['id_customer' => $this->customer->id_customer, 'no_polisi' => 'B 1234 ABC', 'merk' => 'Toyota', 'model' => 'Fortuner']);
        $productType = ProductType::factory()->create(['order_category' => 'AUTOMOTIVE']);
        $product = Product::factory()->create(['nama_produk' => 'LEXENT HT', 'id_product_type' => $productType->id_product_type, 'status' => 'enabled', 'is_warranty_eligible' => true]);

        $idOrder = $this->postJson('/order/store', [
            'order_type' => 'AUTOMOTIVE',
            'brand' => 'LEXENT',
            'id_customer' => $this->customer->id_customer,
            'id_vehicle' => $vehicle->id_vehicle,
            'id_technician' => Technician::factory()->create()->id_technician,
            'order_date' => now()->toDateString(),
            'discount' => 0,
            'details' => [[
                'id_treatment' => Treatment::factory()->create(['code' => 'COATING', 'order_category' => 'AUTOMOTIVE'])->id_treatment,
                'id_product' => $product->id_product,
                'area' => 'Kaca Depan',
                'quantity' => 1,
                'unit_price' => 500000,
                'warranty_months' => 6,
            ]],
        ])->assertStatus(201)->json('id_order');
        $idInvoice = $this->postJson("/invoice/generate/{$idOrder}")->assertOk()->json('id_invoice');
        $this->postJson("/invoice/{$idInvoice}/payment", ['payment_date' => now()->toDateString(), 'amount' => 500000, 'payment_method' => 'CASH'])->assertOk();
        $this->kode = $this->postJson("/order/{$idOrder}/generate-warranty")->assertOk()->json('kode');

        // The public page is for customers: no login.
        auth()->logout();
    }

    public function test_public_page_shows_the_warranty_details_without_login(): void
    {
        $this->get('/warranty/' . $this->kode)
            ->assertOk()
            ->assertSee('E-WARRANTY CARD')
            ->assertSee($this->kode)
            ->assertSee('Budi Santoso')
            ->assertSee('081234567890')
            ->assertSee('Jl. Melati No. 10')
            ->assertSee('B 1234 ABC')
            ->assertSee('LEXENT HT')
            ->assertSee('Kaca Depan')
            ->assertSee('6 Bulan')
            ->assertSee('WARRANTY ACTIVE')
            ->assertSee('images/warranty-cover.jpg', false);
    }

    public function test_page_uses_the_cover_artwork_and_no_qr_code(): void
    {
        $html = $this->get('/warranty/' . $this->kode)->assertOk()->getContent();

        $this->assertStringNotContainsStringIgnoringCase('qrcode', $html);
        $this->assertStringNotContainsString('Scan QR', $html);
        $this->assertFileExists(public_path('images/warranty-cover.jpg'));
        $this->assertFileExists(public_path('images/lexent-logo.png'));
    }

    public function test_customer_service_contact_comes_from_config(): void
    {
        $this->get('/warranty/' . $this->kode)->assertSee('0815 1345 5525')->assertSee('www.lexent.id');

        config(['warranty_card.customer_service_whatsapp' => '0811 000 111', 'warranty_card.website' => 'www.contoh.test']);

        $this->get('/warranty/' . $this->kode)->assertSee('0811 000 111')->assertSee('www.contoh.test')->assertDontSee('0815 1345 5525');
    }

    public function test_expired_warranty_is_labelled_expired(): void
    {
        Warranty::where('kode_warranty', $this->kode)->update(['tanggal_expired' => now()->subDay()->toDateString()]);

        $this->get('/warranty/' . $this->kode)->assertOk()->assertSee('WARRANTY EXPIRED');
    }

    public function test_void_or_unknown_warranty_is_not_found(): void
    {
        $this->get('/warranty/WR9999999')->assertNotFound();

        Warranty::where('kode_warranty', $this->kode)->update(['status' => 'Void']);
        $this->get('/warranty/' . $this->kode)->assertNotFound();
    }

    public function test_pin_is_not_required(): void
    {
        $this->get('/warranty/' . $this->kode)->assertDontSee('PIN')->assertDontSee('Verifikasi');
    }
}
