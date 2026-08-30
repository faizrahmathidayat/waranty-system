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
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderInvoiceWarrantyFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Order/invoice/payment stores are all POSTed as plain form submissions
        // without a wired-up CSRF token in these tests -- disable the check
        // here rather than plumb a token through every request.
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->actingAs(Login::factory()->create());
    }

    private function makeAutomotiveOrder(int $warrantyMonths = 6): array
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
            'masa_garansi_bulan' => $warrantyMonths,
        ]);

        return compact('customer', 'vehicle', 'technician', 'productType', 'treatment', 'product');
    }

    private function storeOrder(array $parts): int
    {
        $response = $this->postJson('/order/store', [
            'order_type' => 'AUTOMOTIVE',
            'id_customer' => $parts['customer']->id_customer,
            'id_vehicle' => $parts['vehicle']->id_vehicle,
            'id_technician' => $parts['technician']->id_technician,
            'order_date' => now()->toDateString(),
            'discount' => 0,
            'details' => [[
                'id_treatment' => $parts['treatment']->id_treatment,
                'id_product' => $parts['product']->id_product,
                'area' => 'Full Body',
                'quantity' => 1,
                'unit_price' => 500000,
            ]],
        ]);

        $response->assertStatus(201)->assertJson(['success' => true]);

        return $response->json('id_order');
    }

    public function test_full_order_to_warranty_happy_path(): void
    {
        $parts = $this->makeAutomotiveOrder(6);

        // 1. Create the order.
        $idOrder = $this->storeOrder($parts);
        $order = Order::findOrFail($idOrder);
        $this->assertSame('OPEN', $order->status);
        $this->assertStringStartsWith('ORD'.now()->format('Y'), $order->order_number);
        $this->assertSame(1, $order->details()->count());
        $this->assertEquals(500000, (float) $order->grand_total);

        // 2. Generate an invoice from the order.
        $invoiceResponse = $this->postJson("/invoice/generate/{$idOrder}");
        $invoiceResponse->assertStatus(200)->assertJson(['success' => true]);
        $idInvoice = $invoiceResponse->json('id_invoice');

        $order->refresh();
        $this->assertSame('INVOICED', $order->status);

        $invoice = \App\Models\Invoice::findOrFail($idInvoice);
        $this->assertSame('OPEN', $invoice->status);
        $this->assertEquals(500000, (float) $invoice->grand_total);
        $this->assertEquals(500000, (float) $invoice->outstanding_amount);

        // A second invoice cannot be generated while one is already active.
        $this->postJson("/invoice/generate/{$idOrder}")->assertStatus(422);

        // 3. Pay the invoice in full.
        $paymentResponse = $this->postJson("/invoice/{$idInvoice}/payment", [
            'payment_date' => now()->toDateString(),
            'amount' => 500000,
            'payment_method' => 'CASH',
        ]);
        $paymentResponse->assertStatus(200)->assertJson(['success' => true]);

        $invoice->refresh();
        $order->refresh();
        $this->assertSame('PAID', $invoice->status);
        $this->assertEquals(0, (float) $invoice->outstanding_amount);
        $this->assertSame('COMPLETED', $order->status);

        // Paying an already-settled invoice must be rejected.
        $this->postJson("/invoice/{$idInvoice}/payment", [
            'payment_date' => now()->toDateString(),
            'amount' => 1,
            'payment_method' => 'CASH',
        ])->assertStatus(422);

        // 4. Generate the warranty.
        $warrantyResponse = $this->postJson("/order/{$idOrder}/generate-warranty");
        $warrantyResponse->assertStatus(200)->assertJson(['success' => true]);
        $kode = $warrantyResponse->json('kode');

        $this->assertStringStartsWith('WR'.date('Y'), $kode);

        $warranty = Warranty::where('kode_warranty', $kode)->firstOrFail();
        $this->assertSame($parts['customer']->id_customer, $warranty->id_customer);
        $this->assertSame($idOrder, $warranty->id_order);
        $this->assertSame($idInvoice, $warranty->id_invoice);
        $this->assertSame('Active', $warranty->status);
        // Warranty::tanggal_expired isn't cast to a date (unlike Order/Invoice's
        // date columns), so it comes back as a plain "Y-m-d" string.
        $this->assertSame(
            $order->order_date->copy()->addMonths(6)->toDateString(),
            $warranty->tanggal_expired
        );
        $this->assertSame(1, $warranty->warrantyItems()->count());

        // Generating a second warranty for the same order must be rejected.
        $this->postJson("/order/{$idOrder}/generate-warranty")->assertStatus(422);
    }

    public function test_warranty_cannot_be_generated_before_the_order_is_completed(): void
    {
        // ServiceCompletionController::generate() gates on order.status === 'COMPLETED'
        // first -- and PaymentController only ever flips an order to COMPLETED in the
        // same transaction it marks the invoice PAID, so "order" is the error this
        // reaches in practice at every earlier stage, not "invoice".
        $parts = $this->makeAutomotiveOrder();
        $idOrder = $this->storeOrder($parts);

        $this->postJson("/order/{$idOrder}/generate-warranty")
            ->assertStatus(422)
            ->assertJsonValidationErrors('order');

        $invoiceResponse = $this->postJson("/invoice/generate/{$idOrder}");
        $idInvoice = $invoiceResponse->json('id_invoice');

        // Invoice exists but is not yet paid -- order is still only INVOICED.
        $this->postJson("/order/{$idOrder}/generate-warranty")
            ->assertStatus(422)
            ->assertJsonValidationErrors('order');

        // Partial payment still leaves outstanding balance -- order stays INVOICED.
        $this->postJson("/invoice/{$idInvoice}/payment", [
            'payment_date' => now()->toDateString(),
            'amount' => 200000,
            'payment_method' => 'CASH',
        ])->assertStatus(200);

        $this->postJson("/order/{$idOrder}/generate-warranty")
            ->assertStatus(422)
            ->assertJsonValidationErrors('order');
    }

    public function test_invoice_cannot_be_generated_from_an_order_without_details(): void
    {
        $parts = $this->makeAutomotiveOrder();

        $order = Order::create([
            'order_number' => 'ORD'.now()->format('Y').'9999',
            'id_customer' => $parts['customer']->id_customer,
            'order_type' => 'AUTOMOTIVE',
            'id_vehicle' => $parts['vehicle']->id_vehicle,
            'id_technician' => $parts['technician']->id_technician,
            'order_date' => now()->toDateString(),
            'status' => 'OPEN',
            'subtotal' => 0,
            'discount' => 0,
            'grand_total' => 0,
        ]);

        $this->postJson("/invoice/generate/{$order->id_order}")
            ->assertStatus(422)
            ->assertJsonValidationErrors('order');
    }
}
