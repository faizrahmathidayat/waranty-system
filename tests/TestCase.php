<?php

namespace Tests;

use App\Models\BankAccount;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /** The invoice print endpoint streams a PDF, so assertions on its content run against the rendered view. */
    protected function invoicePrint($idInvoice, int $design = 1): TestResponse
    {
        $invoice = Invoice::with(['items.orderDetail.product.productType', 'order.vehicle', 'order.building', 'customer'])->findOrFail($idInvoice);
        $bankAccounts = BankAccount::active()->orderBy('bank_name')->get();

        return TestResponse::fromBaseResponse(new Response(view('invoice.print', compact('invoice', 'bankAccounts', 'design'))->render()));
    }
}
