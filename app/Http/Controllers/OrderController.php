<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Building;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Treatment;
use App\Models\Technician;
use App\Models\Vehicle;
use App\Services\AutomotiveOrderCatalog;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    private function ensureAuthenticated(): void
    {
        if (empty(Auth::user()->username)) abort(403);
    }

    public function index()
    {
        $this->ensureAuthenticated();

        return view('order.index', [
            'title'  => 'Order',
            'navbar' => 'Order'
        ]);
    }

    public function data(Request $request)
    {
        $this->ensureAuthenticated();
        $query = Order::query()
            ->leftJoin('customers', 'customers.id_customer', '=', 'orders.id_customer')
            ->leftJoin('vehicles', 'vehicles.id_vehicle', '=', 'orders.id_vehicle')
            ->leftJoin('buildings', 'buildings.id_building', '=', 'orders.id_building')
            ->select('orders.*', 'customers.nama_customer', 'vehicles.no_polisi', 'vehicles.merk', 'vehicles.model', 'buildings.nama_bangunan', 'buildings.alamat as building_alamat')
            ->withCount('details');
        if ($request->filled('status')) $query->where('orders.status', $request->status);
        if ($request->filled('id_customer')) $query->where('orders.id_customer', $request->id_customer);
        if ($request->filled('order_type')) $query->where('orders.order_type', $request->order_type);
        return DataTables::of($query)->toJson();
    }

    public function create()
    {
        $this->ensureAuthenticated();
        return view('order.form', $this->formData());
    }

    public function show($id)
    {
        $this->ensureAuthenticated();
        $order = $this->order($id)->load(['customer', 'vehicle', 'building', 'technician', 'activeInvoice', 'invoices', 'warranties.warrantyItems', 'details.treatment', 'details.product', 'details.productVariant']);
        return view('order.show', [
            'order' => $order,
            'navbar' => 'Order',
        ]);
    }

    public function edit($id)
    {
        $this->ensureAuthenticated();
        $order = $this->order($id)->load(['vehicle', 'building', 'technician', 'details.treatment', 'details.product', 'details.productVariant']);
        abort_unless($order->status === 'OPEN', 403, 'Hanya Order OPEN yang dapat diubah.');
        return view('order.form', $this->formData($order));
    }

    public function store(Request $request)
    {
        $this->ensureAuthenticated();
        try {
            $order = $this->saveOrder($request);
            return response()->json(['success' => true, 'id_order' => $order->id_order, 'redirect' => url('/order?notice=created')], 201);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Order store failed.', ['exception' => $exception]);
            return response()->json(['message' => 'Gagal menyimpan Order.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $this->ensureAuthenticated();
        $order = $this->order($id);
        abort_unless($order->status === 'OPEN', 403, 'Hanya Order OPEN yang dapat diubah.');
        try {
            $order = $this->saveOrder($request, $order);
            return response()->json(['success' => true, 'id_order' => $order->id_order, 'redirect' => url('/order?notice=updated')]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Order update failed.', ['id_order' => $id, 'exception' => $exception]);
            return response()->json(['message' => 'Gagal menyimpan Order.'], 500);
        }
    }

    public function confirm($id)
    {
        $this->ensureAuthenticated();
        $order = $this->order($id);
        if ($order->status !== 'OPEN') return response()->json(['message' => 'Hanya Order OPEN yang dapat diproses.'], 422);
        try {
            $order->update(['status' => 'OPEN', 'updated_by' => Auth::id()]);
            return response()->json(['success' => true]);
        } catch (\Throwable $exception) {
            Log::error('Order confirmation failed.', ['id_order' => $id, 'exception' => $exception]);
            return response()->json(['message' => 'Gagal memperbarui status Order.'], 500);
        }
    }

    public function cancel($id)
    {
        $this->ensureAuthenticated();
        $order = $this->order($id);
        if ($order->status !== 'OPEN') return response()->json(['message' => 'Order hanya dapat dibatalkan saat OPEN. Batalkan Invoice aktif terlebih dahulu.'], 422);
        try {
            $order->update(['status' => 'CANCELLED', 'updated_by' => Auth::id()]);
            return response()->json(['success' => true]);
        } catch (\Throwable $exception) {
            Log::error('Order cancellation failed.', ['id_order' => $id, 'exception' => $exception]);
            return response()->json(['message' => 'Gagal membatalkan Order.'], 500);
        }
    }

    public function customerVehicles($idCustomer)
    {
        $this->ensureAuthenticated();
        return response()->json(Vehicle::where('id_customer', $idCustomer)->where('status', 'active')->orderBy('no_polisi')->get(['id_vehicle', 'no_polisi', 'merk', 'model', 'tahun']));
    }

    public function customerBuildings($idCustomer)
    {
        $this->ensureAuthenticated();
        return response()->json(Building::where('id_customer', $idCustomer)->where('status', 'active')->orderBy('nama_bangunan')->get(['id_building', 'nama_bangunan', 'alamat']));
    }

    public function treatmentProducts(Request $request, $idTreatment)
    {
        $this->ensureAuthenticated();
        $orderType = $request->validate(['order_type' => 'required|in:AUTOMOTIVE,BUILDING'])['order_type'];
        Treatment::where('is_active', true)->where('order_category', $orderType)->findOrFail($idTreatment);
        $products = Product::query()->join('product_types', 'product_types.id_product_type', '=', 'products.id_product_type')
            ->where('products.status', 'enabled')->where('product_types.is_active', true)->where('product_types.order_category', $orderType)
            ->orderBy('products.brand')->orderBy('products.nama_produk')
            ->get(['products.id_product', 'products.brand', 'products.nama_produk', 'products.harga_default', 'products.masa_garansi_bulan', 'products.is_warranty_eligible']);
        return response()->json(['data' => $products]);
    }

    public function productVariants($idProduct)
    {
        $this->ensureAuthenticated();
        return response()->json(ProductVariant::where('id_product', $idProduct)->where('is_active', true)->orderBy('name')->get(['id_product_variant', 'code', 'name', 'value', 'unit', 'harga_tambahan']));
    }

    private function formData(?Order $order = null): array
    {
        return [
            'title' => $order ? 'Edit Order ' . $order->order_number : 'Buat Order',
            'navbar' => 'Order',
            'order' => $order,
            'customers' => Customer::where('status', 'enabled')->orderBy('nama_customer')->get(['id_customer', 'nama_customer', 'no_hp']),
            'technicians' => Technician::where('is_active', true)->orderBy('name')->get(['id_technician', 'code', 'name']),
            'treatments' => Treatment::where('is_active', true)->orderBy('name')->get(['id_treatment', 'code', 'name', 'order_category']),
        ];
    }

    private function order($id): Order
    {
        return Order::findOrFail($id);
    }

    private function saveOrder(Request $request, ?Order $existing = null): Order
    {
        $header = Validator::make($request->all(), [
            'order_type' => 'required|in:AUTOMOTIVE,BUILDING',
            'id_customer' => 'required|exists:customers,id_customer',
            'id_vehicle' => 'nullable|exists:vehicles,id_vehicle',
            'id_building' => 'nullable|exists:buildings,id_building',
            'id_technician' => 'required|exists:technicians,id_technician',
            'order_date' => 'required|date',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'details' => 'required|array|min:1',
        ], [
            'id_customer.required' => 'Customer harus diisi terlebih dahulu.',
            'id_vehicle.required' => 'Vehicle harus diisi terlebih dahulu.',
            'id_building.required' => 'Building harus diisi terlebih dahulu.',
            'id_technician.required' => 'Teknisi harus diisi terlebih dahulu.',
            'order_date.required' => 'Tanggal Order harus diisi terlebih dahulu.',
            'details.required' => 'Detail Order harus diisi terlebih dahulu.',
            'details.min' => 'Minimal satu item Order harus diisi.',
        ])->validate();
        $customer = Customer::where('id_customer', $header['id_customer'])->where('status', 'enabled')->first();
        if (!$customer) throw ValidationException::withMessages(['id_customer' => 'Customer harus berstatus enabled.']);
        if (!Technician::where('id_technician', $header['id_technician'])->where('is_active', true)->exists()) throw ValidationException::withMessages(['id_technician' => 'Teknisi harus aktif.']);
        if ($header['order_type'] === 'AUTOMOTIVE') {
            if (empty($header['id_vehicle']) || !Vehicle::where('id_vehicle', $header['id_vehicle'])->where('id_customer', $customer->id_customer)->where('status', 'active')->exists()) {
                throw ValidationException::withMessages(['id_vehicle' => 'Vehicle wajib dipilih dan harus milik customer yang dipilih.']);
            }
        } else {
            if (empty($header['id_building']) || !Building::where('id_building', $header['id_building'])->where('id_customer', $customer->id_customer)->where('status', 'active')->exists()) {
                throw ValidationException::withMessages(['id_building' => 'Building wajib dipilih dan harus milik customer yang dipilih.']);
            }
        }
        $details = $this->validatedDetails($header['details'], $header['order_type']);
        $subtotal = round(collect($details)->sum('subtotal'), 2);
        $discount = round((float) ($header['discount'] ?? 0), 2);
        if ($discount > $subtotal) throw ValidationException::withMessages(['discount' => 'Discount header tidak boleh melebihi subtotal.']);

        // The unique database index is the final duplicate guard. Retry is for a
        // concurrent first order in the same year.
        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                return DB::transaction(function () use ($existing, $header, $details, $subtotal, $discount) {
                    $attributes = ['id_customer' => $header['id_customer'], 'order_type' => $header['order_type'], 'id_vehicle' => $header['order_type'] === 'AUTOMOTIVE' ? $header['id_vehicle'] : null, 'id_building' => $header['order_type'] === 'BUILDING' ? $header['id_building'] : null, 'id_technician' => $header['id_technician'], 'order_date' => $header['order_date'], 'subtotal' => $subtotal, 'discount' => $discount, 'grand_total' => $subtotal - $discount, 'notes' => $header['notes'] ?? null, 'updated_by' => Auth::id()];
                    if ($existing) {
                        $order = Order::lockForUpdate()->findOrFail($existing->id_order);
                        $order->update($attributes);
                        $order->details()->delete();
                    } else {
                        $attributes['order_number'] = $this->nextOrderNumber();
                        $attributes['status'] = 'OPEN';
                        $attributes['created_by'] = Auth::id();
                        $order = Order::create($attributes);
                    }
                    foreach ($details as $detail) $order->details()->create($detail);
                    return $order;
                }, 3);
            } catch (QueryException $exception) {
                if ($attempt === 2 || !str_contains(strtolower($exception->getMessage()), 'order_number')) throw $exception;
            }
        }
        throw new \RuntimeException('Tidak dapat membuat nomor order.');
    }

    private function validatedDetails(array $items, string $orderType): array
    {
        $result = [];
        $duplicates = [];
        foreach ($items as $index => $item) {
            $data = Validator::make($item, ['id_treatment' => 'required|integer', 'id_product' => 'required|integer', 'id_product_variant' => 'nullable|integer', 'area' => $orderType === 'BUILDING' ? 'required|string|max:150' : 'nullable|string|max:150', 'area_custom' => 'nullable|string|max:150', 'total_luas' => $orderType === 'BUILDING' ? 'required|numeric|gt:0' : 'nullable|numeric', 'panjang' => 'nullable|numeric', 'lebar' => 'nullable|numeric', 'quantity' => 'required|numeric|gt:0', 'unit' => 'nullable|in:unit,m2,panel', 'unit_price' => 'nullable|numeric|min:0', 'discount' => 'nullable|numeric|min:0', 'discount_type' => 'nullable|in:NOMINAL,PERCENT'], [
                'id_treatment.required' => 'Treatment harus diisi terlebih dahulu.', 'id_treatment.integer' => 'Treatment tidak valid.',
                'id_product.required' => 'Product harus diisi terlebih dahulu.', 'id_product.integer' => 'Product tidak valid.',
                'area.required' => 'Area harus diisi terlebih dahulu.',
                'total_luas.required' => 'Luas harus diisi terlebih dahulu.', 'total_luas.gt' => 'Luas harus lebih besar dari nol.',
                'quantity.required' => 'Quantity harus diisi terlebih dahulu.', 'quantity.gt' => 'Quantity harus lebih besar dari nol.',
                'unit_price.numeric' => 'Harga harus berupa angka.', 'unit_price.min' => 'Harga tidak boleh negatif.',
                'discount.numeric' => 'Discount harus berupa angka.', 'discount.min' => 'Discount tidak boleh negatif.',
                'discount_type.in' => 'Jenis discount tidak valid.',
            ])->validate();
            $treatment = Treatment::where('is_active', true)->find($data['id_treatment']);
            if (!$treatment || $treatment->order_category !== $orderType) throw ValidationException::withMessages(["details.$index.id_treatment" => 'Treatment tidak valid untuk kategori Order ini.']);
            $product = Product::query()->join('product_types', 'product_types.id_product_type', '=', 'products.id_product_type')->where('products.id_product', $data['id_product'])->where('products.status', 'enabled')->where('product_types.is_active', true)->where('product_types.order_category', $orderType)->select('products.*')->first();
            if (!$product) throw ValidationException::withMessages(["details.$index.id_product" => 'Product tidak sesuai dengan kategori Order atau tidak aktif.']);
            $variants = ProductVariant::where('id_product', $product->id_product)->where('is_active', true)->get();
            $variant = null;
            if ($data['id_product_variant'] ?? null) $variant = $variants->firstWhere('id_product_variant', (int) $data['id_product_variant']);
            if (($orderType === 'AUTOMOTIVE' && $treatment->code === 'KACA_FILM' && $variants->isNotEmpty() && !$variant) || (($data['id_product_variant'] ?? null) && !$variant)) throw ValidationException::withMessages(["details.$index.id_product_variant" => 'Variant aktif wajib dipilih dan harus milik product.']);
            $area = ($data['area'] ?? '') === 'Lainnya' ? trim((string) ($data['area_custom'] ?? '')) : trim((string) ($data['area'] ?? ''));
            $area = $this->normalizeAutomotiveArea($area, $treatment->code);
            if (($orderType === 'BUILDING' || AutomotiveOrderCatalog::requiresArea($treatment->code)) && $area === '') throw ValidationException::withMessages(["details.$index.area" => 'Area wajib diisi untuk treatment ini.']);
            // Area is entered manually in the current Order form. Keep the
            // catalogue for suggestions/normalisation, but accept a clear
            // custom area such as "Full selain kaca depan".
            $quantity = round((float) $data['quantity'], 2);
            // Default to the master price, but allow an order-level price
            // adjustment as the transaction snapshot.
            $unitPrice = array_key_exists('unit_price', $data) && $data['unit_price'] !== null && $data['unit_price'] !== ''
                ? round((float) $data['unit_price'], 2)
                : round((float) ($product->harga_default ?? 0), 2);
            $discountValue = round((float) ($data['discount'] ?? 0), 2);
            $discountType = $data['discount_type'] ?? 'NOMINAL';
            // Building orders now receive a final area (m²), not dimensions.
            // Keep dimension columns null so existing historical records remain intact.
            $panjang = null;
            $lebar = null;
            $totalLuas = $orderType === 'BUILDING' ? round((float) $data['total_luas'], 2) : null;
            $luasPerItem = $orderType === 'BUILDING' ? round($totalLuas / $quantity, 2) : null;
            $unit = ($data['unit'] ?? null) ?: ($orderType === 'BUILDING' ? 'm2' : 'unit');
            // Luas is operational information. Billing is always based on
            // item quantity, including Building orders.
            $gross = round($quantity * $unitPrice, 2);
            if ($discountType === 'PERCENT' && $discountValue > 100) {
                throw ValidationException::withMessages(["details.$index.discount" => 'Discount persentase tidak boleh lebih dari 100%.']);
            }
            $discount = $discountType === 'PERCENT'
                ? round($gross * $discountValue / 100, 2)
                : $discountValue;
            if ($discount > $gross) throw ValidationException::withMessages(["details.$index.discount" => 'Discount item tidak boleh melebihi gross item.']);
            $key = implode('|', [$treatment->id_treatment, mb_strtolower($area), $product->id_product, optional($variant)->id_product_variant]);
            if (isset($duplicates[$key])) throw ValidationException::withMessages(["details.$index" => 'Item dengan treatment, area, product, dan variant yang sama sudah ada.']);
            $duplicates[$key] = true;
            $result[] = ['id_treatment' => $treatment->id_treatment, 'id_product' => $product->id_product, 'id_product_variant' => optional($variant)->id_product_variant, 'area' => $area ?: null, 'item_type' => $orderType, 'quantity' => $quantity, 'unit' => $unit, 'panjang' => $panjang, 'lebar' => $lebar, 'luas_per_item' => $luasPerItem, 'total_luas' => $totalLuas, 'unit_price' => $unitPrice, 'discount' => $discount, 'subtotal' => $gross - $discount, 'warranty_eligible' => (bool) $product->is_warranty_eligible, 'warranty_months_snapshot' => $product->masa_garansi_bulan, 'service_status' => 'PENDING', 'product_name_snapshot' => trim($product->brand . ' - ' . $product->nama_produk, ' - '), 'variant_name_snapshot' => optional($variant)->name, 'treatment_name_snapshot' => $treatment->name, 'notes' => null];
        }
        return $result;
    }

    private function nextOrderNumber(): string
    {
        $prefix = 'ORD' . now()->format('Y');
        $last = Order::where('order_number', 'like', $prefix . '%')->lockForUpdate()->orderByDesc('order_number')->value('order_number');
        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function normalizeAutomotiveArea(string $area, string $treatmentCode): string
    {
        if ($treatmentCode !== 'KACA_FILM') return $area;
        $key = mb_strtolower(trim($area));
        return [
            'kaca depan' => 'Kaca Depan',
            'kaca belakang' => 'Kaca Belakang',
            'kaca kiri depan' => 'Samping Kiri Depan',
            'kaca kanan depan' => 'Samping Kanan Depan',
            'kaca kiri belakang' => 'Samping Kiri Belakang',
            'kaca kanan belakang' => 'Samping Kanan Belakang',
            'samping kiri depan' => 'Samping Kiri Depan',
            'samping kanan depan' => 'Samping Kanan Depan',
            'samping kiri belakang' => 'Samping Kiri Belakang',
            'samping kanan belakang' => 'Samping Kanan Belakang',
            'sunroof' => 'Sunroof',
        ][$key] ?? $area;
    }
}
