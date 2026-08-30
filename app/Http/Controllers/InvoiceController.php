<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    private function auth(): void { if (empty(Auth::user()->username)) abort(403); }

    public function index() { $this->auth(); return view('invoice.index', ['title' => 'Invoice', 'navbar' => 'Invoice']); }

    public function data(Request $request)
    {
        $this->auth();
        $query = Invoice::query()->join('orders', 'orders.id_order', '=', 'invoices.id_order')->join('customers', 'customers.id_customer', '=', 'invoices.id_customer')
            ->select('invoices.*', 'orders.order_number', 'orders.order_type', 'customers.nama_customer');
        if ($request->filled('status')) $query->where('invoices.status', $request->status);
        if ($request->filled('id_customer')) $query->where('invoices.id_customer', $request->id_customer);
        if ($request->filled('order_type')) $query->where('orders.order_type', $request->order_type);
        return DataTables::of($query)->toJson();
    }

    public function generate($idOrder)
    {
        $this->auth();
        try { $invoice = $this->createFromOrder($idOrder); return response()->json(['success' => true, 'id_invoice' => $invoice->id_invoice, 'message' => 'Invoice berhasil dibuat.']);
        } catch (ValidationException $exception) { throw $exception;
        } catch (\Throwable $exception) { Log::error('Invoice generation failed.', ['id_order' => $idOrder, 'exception' => $exception]); return response()->json(['message' => 'Gagal membuat Invoice.'], 500); }
    }

    public function show($id)
    {
        $this->auth();
        $invoice = Invoice::with(['items', 'payments', 'order.vehicle', 'order.building', 'order.details', 'order.warranties.warrantyItems', 'customer'])->findOrFail($id);
        return view('invoice.show', compact('invoice') + ['navbar' => 'Invoice']);
    }

    public function cancel($id)
    {
        $this->auth();
        try {
            DB::transaction(function () use ($id) {
                $invoice = Invoice::lockForUpdate()->findOrFail($id);
                $order = Order::lockForUpdate()->findOrFail($invoice->id_order);
                if ($invoice->status !== 'OPEN' || (float) $invoice->paid_amount > 0) throw ValidationException::withMessages(['invoice' => 'Invoice sudah memiliki pembayaran dan tidak dapat dibatalkan.']);
                $invoice->update(['status' => 'CANCELLED', 'updated_by' => Auth::id()]);
                $order->update(['status' => 'OPEN', 'updated_by' => Auth::id()]);
            }, 3);
            return response()->json(['success' => true]);
        } catch (ValidationException $exception) { throw $exception;
        } catch (\Throwable $exception) { Log::error('Invoice cancellation failed.', ['id_invoice' => $id, 'exception' => $exception]); return response()->json(['message' => 'Gagal membatalkan Invoice.'], 500); }
    }

    public function print($id) { $this->auth(); $invoice = Invoice::with(['items', 'order.vehicle', 'order.building', 'customer'])->findOrFail($id); return view('invoice.print', compact('invoice')); }

    private function createFromOrder($idOrder): Invoice
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                return DB::transaction(function () use ($idOrder) {
                    $order = Order::with('details')->lockForUpdate()->findOrFail($idOrder);
                    if ($order->status !== 'OPEN') throw ValidationException::withMessages(['order' => 'Invoice hanya dapat dibuat dari Order OPEN.']);
                    if (Invoice::where('id_order', $order->id_order)->whereIn('status', ['OPEN', 'PARTIAL', 'PAID'])->exists()) throw ValidationException::withMessages(['order' => 'Order masih memiliki Invoice aktif.']);
                    if ($order->details->isEmpty()) throw ValidationException::withMessages(['order' => 'Order tidak memiliki detail.']);
                    $invoice = Invoice::create(['invoice_number' => $this->nextNumber(), 'id_order' => $order->id_order, 'id_customer' => $order->id_customer, 'invoice_date' => now()->toDateString(), 'due_date' => null, 'status' => 'OPEN', 'notes' => $order->notes, 'created_by' => Auth::id(), 'updated_by' => Auth::id()]);
                    foreach ($order->details as $detail) {
                        if ($detail->subtotal < 0 || !$detail->product_name_snapshot || !$detail->treatment_name_snapshot) throw ValidationException::withMessages(['order' => 'Snapshot detail Order tidak valid.']);
                        $invoice->items()->create(['id_order_detail' => $detail->id_order_detail, 'product_name' => $detail->product_name_snapshot, 'variant_name' => $detail->variant_name_snapshot, 'treatment_name' => $detail->treatment_name_snapshot, 'area' => $detail->area, 'quantity' => $detail->quantity, 'unit' => $detail->unit, 'panjang' => $detail->panjang, 'lebar' => $detail->lebar, 'total_luas' => $detail->total_luas, 'unit_price' => $detail->unit_price, 'discount' => $detail->discount, 'subtotal' => $detail->subtotal]);
                    }
                    $subtotal = round((float) $invoice->items()->sum('subtotal'), 2); $discount = round((float) $order->discount, 2);
                    if ($subtotal < 0 || $discount > $subtotal) throw ValidationException::withMessages(['order' => 'Total Order tidak valid untuk Invoice.']);
                    $total = $subtotal - $discount;
                    $invoice->update(['subtotal' => $subtotal, 'discount' => $discount, 'tax_amount' => 0, 'grand_total' => $total, 'paid_amount' => 0, 'outstanding_amount' => $total]);
                    $order->update(['status' => 'INVOICED', 'updated_by' => Auth::id()]);
                    return $invoice;
                }, 3);
            } catch (QueryException $exception) { if ($attempt === 2 || !str_contains(strtolower($exception->getMessage()), 'invoice_number')) throw $exception; }
        }
        throw new \RuntimeException('Tidak dapat membuat nomor Invoice.');
    }

    private function nextNumber(): string
    {
        $prefix = 'INV'.now()->format('Y'); $last = Invoice::where('invoice_number', 'like', $prefix.'%')->lockForUpdate()->orderByDesc('invoice_number')->value('invoice_number');
        return $prefix.str_pad((string) (($last ? (int) substr($last, -4) : 0) + 1), 4, '0', STR_PAD_LEFT);
    }
}
