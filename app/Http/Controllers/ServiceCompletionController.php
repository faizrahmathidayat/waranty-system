<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Warranty;
use App\Models\WarrantyType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ServiceCompletionController extends Controller
{
    private function auth(): void { if (empty(Auth::user()->username)) abort(403); }

    public function start($id)
    {
        $this->auth(); $order = Order::findOrFail($id);
        if ($order->status !== 'CONFIRMED') return response()->json(['message' => 'Hanya Order CONFIRMED dapat dimulai.'], 422);
        DB::transaction(function () use ($order) { $order->update(['status' => 'IN_PROGRESS', 'updated_by' => Auth::id()]); $order->details()->where('service_status', 'PENDING')->update(['service_status' => 'IN_PROGRESS']); });
        return response()->json(['success' => true]);
    }

    public function complete($orderId, $detailId)
    {
        $this->auth(); $order = Order::findOrFail($orderId);
        if (!in_array($order->status, ['CONFIRMED', 'IN_PROGRESS'], true)) return response()->json(['message' => 'Order tidak dapat diselesaikan.'], 422);
        DB::transaction(function () use ($order, $detailId) {
            $detail = $order->details()->lockForUpdate()->findOrFail($detailId);
            if ($detail->service_status === 'CANCELLED') throw ValidationException::withMessages(['detail' => 'Item dibatalkan.']);
            $detail->update(['service_status' => 'COMPLETED', 'completed_at' => now()]);
            $active = $order->details()->where('service_status', '!=', 'CANCELLED')->count(); $done = $order->details()->where('service_status', 'COMPLETED')->count();
            $order->update(['status' => $active === $done ? 'COMPLETED' : 'IN_PROGRESS', 'service_completed_at' => $active === $done ? now() : null, 'updated_by' => Auth::id()]);
        });
        return response()->json(['success' => true]);
    }

    public function generate($id)
    {
        $this->auth();
        try {
            $warranty = DB::transaction(function () use ($id) {
                $order = Order::with(['details', 'activeInvoice', 'vehicle', 'building', 'technician'])->lockForUpdate()->findOrFail($id);
                if ($order->status !== 'COMPLETED') throw ValidationException::withMessages(['order' => 'Order harus COMPLETED sebelum generate Warranty.']);
                if (!$order->activeInvoice || $order->activeInvoice->status !== 'PAID' || (float) $order->activeInvoice->outstanding_amount !== 0.0) throw ValidationException::withMessages(['invoice' => 'Invoice harus PAID dan tidak memiliki outstanding sebelum generate Warranty.']);
                if (Warranty::where('id_order', $order->id_order)->exists()) throw ValidationException::withMessages(['order' => 'Warranty sudah tersedia.']);
                $items = $order->details->where('warranty_eligible', true);
                if ($items->isEmpty()) throw ValidationException::withMessages(['order' => 'Tidak ada item yang memiliki Warranty.']);
                foreach ($items as $item) if (!$item->warranty_months_snapshot) throw ValidationException::withMessages(['order' => 'Masa Warranty item tidak valid.']);
                $typeCode = $order->order_type === 'BUILDING' ? 'BUILDING' : 'CAR';
                $type = WarrantyType::firstOrCreate(
                    ['code' => $typeCode],
                    ['name' => $typeCode === 'BUILDING' ? 'BUILDING / BANGUNAN' : 'MOBIL / AUTOMOTIVE', 'is_active' => true]
                );
                if (! $type->is_active) throw ValidationException::withMessages(['order' => 'Jenis Warranty sedang tidak aktif.']);
                $last = Warranty::lockForUpdate()->latest('id_warranty')->value('id_warranty') ?: 0; $first = $items->first();
                $warranty = Warranty::create([
                    'kode_warranty' => 'WR'.date('Y').str_pad($last + 1, 3, '0', STR_PAD_LEFT), 'pin_warranty' => (string) random_int(100000, 999999),
                    'id_customer' => $order->id_customer, 'id_order' => $order->id_order, 'id_invoice' => $order->activeInvoice->id_invoice,
                    'id_vehicle' => $order->id_vehicle, 'id_building' => $order->id_building, 'id_warranty_type' => $type->id, 'user_id' => Auth::id(), 'id_product' => $first->id_product,
                    'no_invoice' => $order->activeInvoice->invoice_number, 'no_polisi' => $order->vehicle?->no_polisi, 'merk_mobil' => $order->vehicle?->merk,
                    'tipe_mobil' => $order->vehicle?->model, 'warna_mobil' => $order->vehicle?->warna, 'tahun_mobil' => $order->vehicle?->tahun,
                    'installer' => $order->technician?->name, 'catatan' => $order->notes, 'tanggal_pasang' => $order->order_date->toDateString(),
                    'tanggal_expired' => $items->max(fn ($item) => $order->order_date->copy()->addMonths($item->warranty_months_snapshot)->toDateString()), 'status' => 'Active',
                ]);
                foreach ($items as $item) {
                    $warranty->warrantyItems()->create([
                        'id_order_detail' => $item->id_order_detail, 'id_treatment' => $item->id_treatment, 'id_product' => $item->id_product, 'id_product_variant' => $item->id_product_variant,
                        'item_type' => $order->order_type, 'area' => $item->area, 'quantity' => $item->quantity, 'unit' => $item->unit, 'panjang' => $item->panjang, 'lebar' => $item->lebar, 'luas_per_item' => $item->luas_per_item, 'total_luas' => $item->total_luas,
                        'tanggal_pasang' => $order->order_date->toDateString(), 'tanggal_expired' => $order->order_date->copy()->addMonths($item->warranty_months_snapshot)->toDateString(), 'status' => 'Active',
                        'product_name_snapshot' => $item->product_name_snapshot, 'variant_name_snapshot' => $item->variant_name_snapshot, 'treatment_name_snapshot' => $item->treatment_name_snapshot, 'catatan' => $order->notes,
                    ]);
                }
                return $warranty;
            });
            $folder = public_path('qrcode'); if (!is_dir($folder)) mkdir($folder, 0755, true);
            file_put_contents($folder.DIRECTORY_SEPARATOR.$warranty->kode_warranty.'.svg', QrCode::size(300)->margin(2)->generate(route('warranty.digital', $warranty->kode_warranty)));
            $warranty->update(['qr_code' => $warranty->kode_warranty.'.svg']);
            return response()->json(['success' => true, 'kode' => $warranty->kode_warranty]);
        } catch (ValidationException $exception) { throw $exception;
        } catch (\Throwable $exception) { Log::error('Warranty generation failed', ['exception' => $exception]); return response()->json(['message' => 'Gagal membuat Warranty.'], 500); }
    }
}
