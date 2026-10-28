<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Building;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductVariant;
use App\Models\Technician;
use App\Models\Vehicle;
use App\Services\AutomotiveOrderCatalog;
use Carbon\Carbon;
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
        $order = $this->order($id)->load(['customer', 'vehicle', 'building', 'technician', 'activeInvoice', 'invoices', 'warranties.warrantyItems', 'details.product', 'details.productVariant']);
        return view('order.show', [
            'order' => $order,
            'navbar' => 'Order',
        ]);
    }

    public function edit($id)
    {
        $this->ensureAuthenticated();
        $order = $this->order($id)->load(['vehicle', 'building', 'technician', 'details.product', 'details.productVariant']);
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

    public function products(Request $request)
    {
        $this->ensureAuthenticated();
        $orderType = $request->validate(['order_type' => 'required|in:AUTOMOTIVE,BUILDING'])['order_type'];
        $products = Product::query()->join('product_types', 'product_types.id_product_type', '=', 'products.id_product_type')
            ->where('products.status', 'enabled')->where('product_types.is_active', true)->where('product_types.order_category', $orderType)
            ->orderBy('products.nama_produk')
            ->get(['products.id_product', 'products.id_product_type', 'products.nama_produk', 'products.harga_default', 'products.is_warranty_eligible']);
        return response()->json(['data' => $products]);
    }

    public function productTypes(Request $request)
    {
        $this->ensureAuthenticated();
        $orderType = $request->validate(['order_type' => 'required|in:AUTOMOTIVE,BUILDING'])['order_type'];
        return response()->json(['data' => ProductType::where('is_active', true)->where('order_category', $orderType)->orderBy('name')->get(['id_product_type', 'name', 'is_area_required'])]);
    }

    public function productVariants($idProduct)
    {
        $this->ensureAuthenticated();
        return response()->json(ProductVariant::where('id_product', $idProduct)->where('is_active', true)->orderBy('name')->get(['id_product_variant', 'name', 'value']));
    }

    private function formData(?Order $order = null): array
    {
        return [
            'title' => $order ? 'Edit Order ' . $order->order_number : 'Buat Order',
            'navbar' => 'Order',
            'order' => $order,
            'customers' => Customer::where('status', 'enabled')->orderBy('nama_customer')->get(['id_customer', 'nama_customer', 'no_hp']),
            'technicians' => Technician::where('is_active', true)->orderBy('name')->get(['id_technician', 'code', 'name']),
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
            'brand' => 'required|in:'.implode(',', array_keys(Order::BRANDS)),
            'id_customer' => 'required|exists:customers,id_customer',
            'id_vehicle' => 'nullable|exists:vehicles,id_vehicle',
            'id_building' => 'nullable|exists:buildings,id_building',
            'id_technician' => 'required|exists:technicians,id_technician',
            'order_date' => 'required|date',
            'subtotal' => $request->input('order_type') === 'AUTOMOTIVE' ? 'required|numeric|min:0' : 'nullable',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'details' => 'required|array|min:1',
        ], [
            'subtotal.required' => 'Subtotal harus diisi terlebih dahulu.', 'subtotal.numeric' => 'Subtotal harus berupa angka.', 'subtotal.min' => 'Subtotal tidak boleh negatif.',
            'brand.required' => 'Brand harus dipilih terlebih dahulu.',
            'brand.in' => 'Brand tidak valid.',
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
        // Areas already saved on the order being edited stay valid, so an older order
        // with a pre-dropdown area can still be saved without being forced to change it.
        $legacyAreas = $existing ? $existing->details()->pluck('area')->filter()->all() : [];
        $details = $this->validatedDetails($header['details'], $header['order_type'], $header['order_date'], $legacyAreas);
        // Automotive items carry no price; the subtotal is typed in by hand.
        $subtotal = $header['order_type'] === 'AUTOMOTIVE' ? round((float) $header['subtotal'], 2) : round(collect($details)->sum('subtotal'), 2);
        $discount = round((float) ($header['discount'] ?? 0), 2);
        if ($discount > $subtotal) throw ValidationException::withMessages(['discount' => 'Discount header tidak boleh melebihi subtotal.']);

        // The unique database index is the final duplicate guard. Retry is for a
        // concurrent first order in the same year.
        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                return DB::transaction(function () use ($existing, $header, $details, $subtotal, $discount) {
                    $attributes = ['id_customer' => $header['id_customer'], 'order_type' => $header['order_type'], 'brand' => $header['brand'], 'id_vehicle' => $header['order_type'] === 'AUTOMOTIVE' ? $header['id_vehicle'] : null, 'id_building' => $header['order_type'] === 'BUILDING' ? $header['id_building'] : null, 'id_technician' => $header['id_technician'], 'order_date' => $header['order_date'], 'subtotal' => $subtotal, 'discount' => $discount, 'grand_total' => $subtotal - $discount, 'notes' => $header['notes'] ?? null, 'updated_by' => Auth::id()];
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

    private function validatedDetails(array $items, string $orderType, string $orderDate, array $legacyAreas = []): array
    {
        $result = [];
        $duplicates = [];
        foreach ($items as $index => $item) {
            $data = Validator::make($item, ['id_product_type' => $orderType === 'AUTOMOTIVE' ? 'required|integer' : 'nullable|integer', 'id_product' => 'required|integer', 'id_product_variant' => 'nullable|integer', 'area' => $orderType === 'BUILDING' ? 'required|string|max:150' : 'nullable|string|max:150', 'area_custom' => 'nullable|string|max:150', 'total_luas' => $orderType === 'BUILDING' ? 'required|numeric|gt:0' : 'nullable|numeric', 'panjang' => 'nullable|numeric', 'lebar' => 'nullable|numeric', 'quantity' => 'required|numeric|gt:0', 'unit' => 'nullable|in:unit,m2,panel', 'unit_price' => 'nullable|numeric|min:0', 'discount' => 'nullable|numeric|min:0', 'discount_type' => 'nullable|in:NOMINAL,PERCENT', 'warranty_months' => 'nullable|integer|min:1|max:600'], [
                'id_product_type.required' => 'Product harus diisi terlebih dahulu.', 'id_product_type.integer' => 'Product tidak valid.',
                'id_product.required' => ($orderType === 'AUTOMOTIVE' ? 'Product Type' : 'Product') . ' harus diisi terlebih dahulu.', 'id_product.integer' => ($orderType === 'AUTOMOTIVE' ? 'Product Type' : 'Product') . ' tidak valid.',
                'area.required' => 'Area harus diisi terlebih dahulu.',
                'total_luas.required' => 'Luas harus diisi terlebih dahulu.', 'total_luas.gt' => 'Luas harus lebih besar dari nol.',
                'quantity.required' => 'Quantity harus diisi terlebih dahulu.', 'quantity.gt' => 'Quantity harus lebih besar dari nol.',
                'unit_price.numeric' => 'Harga harus berupa angka.', 'unit_price.min' => 'Harga tidak boleh negatif.',
                'discount.numeric' => 'Discount harus berupa angka.', 'discount.min' => 'Discount tidak boleh negatif.',
                'discount_type.in' => 'Jenis discount tidak valid.',
                'warranty_months.integer' => 'Masa Garansi harus berupa angka bulat.', 'warranty_months.min' => 'Masa Garansi harus lebih besar dari nol.',
            ])->validate();
            $product = Product::query()->join('product_types', 'product_types.id_product_type', '=', 'products.id_product_type')->where('products.id_product', $data['id_product'])->where('products.status', 'enabled')->where('product_types.is_active', true)->where('product_types.order_category', $orderType)->when($data['id_product_type'] ?? null, fn ($query, $idProductType) => $query->where('products.id_product_type', $idProductType))->select('products.*', 'product_types.code as product_type_code', 'product_types.is_area_required as product_type_area_required')->first();
            if (!$product) throw ValidationException::withMessages(["details.$index.id_product" => ($orderType === 'AUTOMOTIVE' ? 'Product Type' : 'Product') . ' tidak sesuai dengan Product, kategori Order, atau tidak aktif.']);
            $variants = ProductVariant::where('id_product', $product->id_product)->where('is_active', true)->get();
            $variant = null;
            if ($data['id_product_variant'] ?? null) $variant = $variants->firstWhere('id_product_variant', (int) $data['id_product_variant']);
            if (($orderType === 'AUTOMOTIVE' && $product->product_type_code === 'KACA_FILM' && $variants->isNotEmpty() && !$variant) || (($data['id_product_variant'] ?? null) && !$variant)) throw ValidationException::withMessages(["details.$index.id_product_variant" => 'Variant aktif wajib dipilih dan harus milik product.']);
            $areaRequired = $orderType === 'BUILDING' || (bool) $product->product_type_area_required;
            $area = !$areaRequired ? '' : (($data['area'] ?? '') === 'Lainnya' ? trim((string) ($data['area_custom'] ?? '')) : trim((string) ($data['area'] ?? '')));
            if ($areaRequired && $orderType === 'AUTOMOTIVE' && !in_array($area, array_merge(AutomotiveOrderCatalog::VEHICLE_AREAS, $legacyAreas), true)) {
                throw ValidationException::withMessages(["details.$index.area" => 'Area harus dipilih dari daftar: ' . implode(', ', AutomotiveOrderCatalog::VEHICLE_AREAS) . '.']);
            }
            if ($areaRequired && $area === '') throw ValidationException::withMessages(["details.$index.area" => 'Area wajib diisi.']);
            // Area is entered manually in the current Order form. Keep the
            // catalogue for suggestions/normalisation, but accept a clear
            // custom area such as "Full selain kaca depan".
            $quantity = round((float) $data['quantity'], 2);
            // Default to the master price, but allow an order-level price
            // adjustment as the transaction snapshot.
            $unitPrice = array_key_exists('unit_price', $data) && $data['unit_price'] !== null && $data['unit_price'] !== ''
                ? round((float) $data['unit_price'], 2)
                : round((float) ($product->harga_default ?? 0), 2);
            // Automotive items are not priced: the order subtotal is entered on the header.
            if ($orderType === 'AUTOMOTIVE') { $unitPrice = 0.0; $data['discount'] = 0; $data['discount_type'] = 'NOMINAL'; }
            $discountValue = round((float) ($data['discount'] ?? 0), 2);
            $discountType = $data['discount_type'] ?? 'NOMINAL';
            // Building orders now receive a final area (m²), not dimensions.
            // Keep dimension columns null so existing historical records remain intact.
            $panjang = null;
            $lebar = null;
            $totalLuas = $orderType === 'BUILDING' ? round((float) $data['total_luas'], 2) : null;
            $luasPerItem = $orderType === 'BUILDING' ? round($totalLuas / $quantity, 2) : null;
            // Automotive items have no unit choice; always the plain "unit".
            $unit = $orderType === 'BUILDING' ? (($data['unit'] ?? null) ?: 'm2') : 'unit';
            // The discount comes off the unit price first (stored per unit/m²);
            // the subtotal is then (price - discount) x total luas (Building) or
            // x quantity (Automotive and PPF).
            if ($discountType === 'PERCENT' && $discountValue > 100) {
                throw ValidationException::withMessages(["details.$index.discount" => 'Discount persentase tidak boleh lebih dari 100%.']);
            }
            $discount = $discountType === 'PERCENT'
                ? round($unitPrice * $discountValue / 100, 2)
                : $discountValue;
            if ($discount > $unitPrice) throw ValidationException::withMessages(["details.$index.discount" => 'Discount tidak boleh melebihi harga.']);
            $subtotal = round(($orderType === 'BUILDING' ? $totalLuas : $quantity) * ($unitPrice - $discount), 2);
            $key = implode('|', [mb_strtolower($area), $product->id_product, optional($variant)->id_product_variant]);
            if (isset($duplicates[$key])) throw ValidationException::withMessages(["details.$index" => 'Item dengan area, product, dan variant yang sama sudah ada.']);
            $duplicates[$key] = true;
            // Warranty length is entered per order item rather than fixed on the
            // product master, so the expiry date is a snapshot taken from this
            // order's date at the time the item is saved.
            $warrantyEligible = (bool) $product->is_warranty_eligible;
            $warrantyMonths = null;
            $expiredSnapshot = null;
            if ($warrantyEligible) {
                if (($data['warranty_months'] ?? null) === null || $data['warranty_months'] === '') {
                    throw ValidationException::withMessages(["details.$index.warranty_months" => 'Masa Garansi (bulan) wajib diisi untuk product ini.']);
                }
                $warrantyMonths = (int) $data['warranty_months'];
                $expiredSnapshot = Carbon::parse($orderDate)->addMonths($warrantyMonths)->toDateString();
            }
            $result[] = ['id_product' => $product->id_product, 'id_product_variant' => optional($variant)->id_product_variant, 'area' => $area ?: null, 'item_type' => $orderType, 'quantity' => $quantity, 'unit' => $unit, 'panjang' => $panjang, 'lebar' => $lebar, 'luas_per_item' => $luasPerItem, 'total_luas' => $totalLuas, 'unit_price' => $unitPrice, 'discount' => $discount, 'subtotal' => $subtotal, 'warranty_eligible' => $warrantyEligible, 'warranty_months_snapshot' => $warrantyMonths, 'tanggal_expired_snapshot' => $expiredSnapshot, 'service_status' => 'PENDING', 'product_name_snapshot' => $product->nama_produk, 'variant_name_snapshot' => optional($variant)->name, 'notes' => null];
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
}
