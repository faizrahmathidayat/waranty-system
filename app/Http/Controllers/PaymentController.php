<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    private function auth(): void { if (empty(Auth::user()->username)) abort(403); }
    public function history($invoiceId) { $this->auth(); return response()->json(Invoice::findOrFail($invoiceId)->payments()->orderBy('payment_date')->orderBy('id_payment')->get()); }

    public function store(Request $request, $invoiceId)
    {
        $this->auth();
        $data = Validator::make($request->all(), ['payment_date' => 'required|date', 'amount' => 'required|numeric|gt:0', 'payment_method' => 'required|in:CASH,TRANSFER,EDC,QRIS,OTHER', 'reference_number' => 'nullable|string|max:100', 'notes' => 'nullable|string'], [
            'payment_date.required' => 'Tanggal pembayaran harus diisi terlebih dahulu.', 'payment_date.date' => 'Tanggal pembayaran tidak valid.',
            'amount.required' => 'Nominal pembayaran harus diisi terlebih dahulu.', 'amount.numeric' => 'Nominal pembayaran harus berupa angka.', 'amount.gt' => 'Nominal pembayaran harus lebih besar dari nol.',
            'payment_method.required' => 'Metode pembayaran harus diisi terlebih dahulu.', 'payment_method.in' => 'Metode pembayaran tidak valid.',
        ])->validate();
        try {
            $invoice = DB::transaction(function () use ($invoiceId, $data) {
                $invoice = Invoice::lockForUpdate()->findOrFail($invoiceId);
                $order = Order::lockForUpdate()->findOrFail($invoice->id_order);
                if (!in_array($invoice->status, ['OPEN', 'PARTIAL'], true)) throw ValidationException::withMessages(['invoice' => 'Invoice tidak dapat menerima payment.']);
                $outstanding = max(0, round((float) $invoice->grand_total - (float) $invoice->payments()->where('status', 'ACTIVE')->sum('amount'), 2));
                if ($outstanding <= 0) throw ValidationException::withMessages(['invoice' => 'Invoice sudah lunas.']);
                $amount = round((float) $data['amount'], 2);
                if ($amount > $outstanding) throw ValidationException::withMessages(['amount' => 'Payment melebihi outstanding.']);
                // payment_type remains in the legacy table for compatibility,
                // but does not drive status or validation in the new flow.
                $invoice->payments()->create($data + ['payment_type' => 'PAYMENT', 'status' => 'ACTIVE', 'created_by' => Auth::id()]);
                $paid = round((float) $invoice->payments()->where('status', 'ACTIVE')->sum('amount'), 2); $outstanding = max(0, round((float) $invoice->grand_total - $paid, 2));
                $status = $outstanding <= 0 ? 'PAID' : 'PARTIAL';
                $invoice->update(['paid_amount' => $paid, 'outstanding_amount' => $outstanding, 'status' => $status, 'updated_by' => Auth::id()]);
                $order->update(['status' => $status === 'PAID' ? 'COMPLETED' : 'INVOICED', 'updated_by' => Auth::id()]);
                return $invoice->fresh();
            }, 3);
            return response()->json(['success' => true, 'message' => 'Payment berhasil disimpan.', 'invoice' => $invoice]);
        } catch (ValidationException $exception) { throw $exception;
        } catch (\Throwable $exception) { Log::error('Payment store failed.', ['id_invoice' => $invoiceId, 'exception' => $exception]); return response()->json(['message' => 'Gagal menyimpan Payment.'], 500); }
    }

    public function void(Request $request, $paymentId)
    {
        $this->auth();
        $data = Validator::make($request->all(), ['void_reason' => 'nullable|string|max:1000'])->validate();
        try {
            $invoice = DB::transaction(function () use ($paymentId, $data) {
                $payment = DB::table('payments')->where('id_payment', $paymentId)->lockForUpdate()->first();
                if (!$payment) abort(404);
                if ($payment->status === 'VOID') throw ValidationException::withMessages(['payment' => 'Payment sudah di-void.']);
                $invoice = Invoice::lockForUpdate()->findOrFail($payment->id_invoice);
                $order = Order::lockForUpdate()->findOrFail($invoice->id_order);
                if ($order->warranties()->exists()) throw ValidationException::withMessages(['payment' => 'Payment tidak dapat di-void karena Warranty sudah dibuat.']);
                DB::table('payments')->where('id_payment', $paymentId)->update(['status' => 'VOID', 'voided_at' => now(), 'voided_by' => Auth::id(), 'void_reason' => $data['void_reason'] ?? null, 'updated_at' => now()]);
                $paid = round((float) DB::table('payments')->where('id_invoice', $invoice->id_invoice)->where('status', 'ACTIVE')->sum('amount'), 2);
                $outstanding = max(0, round((float) $invoice->grand_total - $paid, 2));
                $status = $paid <= 0 ? 'OPEN' : ($outstanding <= 0 ? 'PAID' : 'PARTIAL');
                $invoice->update(['paid_amount' => $paid, 'outstanding_amount' => $outstanding, 'status' => $status, 'updated_by' => Auth::id()]);
                $order->update(['status' => $status === 'PAID' ? 'COMPLETED' : 'INVOICED', 'updated_by' => Auth::id()]);
                return $invoice->fresh();
            }, 3);
            return response()->json(['success' => true, 'message' => 'Payment berhasil di-void.', 'invoice' => $invoice]);
        } catch (ValidationException $exception) { throw $exception;
        } catch (\Throwable $exception) { Log::error('Payment void failed.', ['id_payment' => $paymentId, 'exception' => $exception]); return response()->json(['message' => 'Gagal melakukan void Payment.'], 500); }
    }
}
