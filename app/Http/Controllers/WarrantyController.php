<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Warranty;
use App\Models\WarrantyBuilding;
use App\Models\WarrantyBuildingItem;
use App\Models\WarrantyPpf;
use App\Models\WarrantyPpfItem;
use App\Models\WarrantyType;
use App\Models\WarrantyVehicle;
use App\Models\WarrantyVehicleItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Yajra\DataTables\Facades\DataTables;


class WarrantyController extends Controller
{

    public function index()
    {
        if (empty(Auth::user()->username)) {
            return redirect()->to('/login');
        }

        $customers = Customer::orderBy('nama_customer')->get();

        $customers_enabled = Customer::where('status', 'enabled')
            ->orderBy('nama_customer')
            ->get();

        $products = Product::orderBy('nama_produk')->get();

        $products_enabled = Product::where('status', 'enabled')
            ->orderBy('nama_produk')
            ->get();
        $warrantyTypes = WarrantyType::where('is_active', true)->orderBy('name')->get();

        return view('warranty.index', [
            'title'     => 'Warranty',
            'navbar'    => 'Warranty',
            'customers' => $customers,
            'products'  => $products,
            'customers_enabled' => $customers_enabled,
            'products_enabled'  => $products_enabled,
            'warrantyTypes' => $warrantyTypes,
        ]);
    }


    // public function data()
    // {
    //     if (empty(Auth::user()->username)) {
    //         return redirect()->to('/login');
    //     }

    //     $warranty = Warranty::select(
    //         'warranties.*',
    //         'customers.nama_customer',
    //         'products.nama_produk'
    //     )
    //         ->leftJoin('customers', 'customers.id_customer', '=', 'warranties.id_customer')
    //         ->leftJoin('products', 'products.id_product', '=', 'warranties.id_product')
    //         ->orderBy('warranties.id_warranty', 'desc')
    //         ->get();

    //     foreach ($warranty as $item) {

    //         if ($item->status == 'Void') {

    //             $item->status_view = 'Void';
    //         } elseif (strtotime($item->tanggal_expired) < strtotime(date('Y-m-d'))) {

    //             $item->status_view = 'Expired';
    //         } else {

    //             $item->status_view = 'Active';
    //         }
    //     }

    //     return DataTables::of($warranty)->toJson();
    // }

    public function data(Request $request)
    {
        if (empty(Auth::user()->username)) {
            return redirect()->to('/login');
        }

        $query = Warranty::query()
            ->leftJoin(
                'customers',
                'customers.id_customer',
                '=',
                'warranties.id_customer'
            )
            ->leftJoin(
                'products',
                'products.id_product',
                '=',
                'warranties.id_product'
            )
            ->select(
                'warranties.*',
                'customers.nama_customer',
                'products.nama_produk'
            )
            ->orderBy('warranties.id_warranty', 'desc');


        // =========================
        // FILTER PRODUCT
        // =========================

        if ($request->filled('product')) {

            $query->where(
                'warranties.id_product',
                $request->product
            );
        }


        // =========================
        // FILTER STATUS
        // =========================

        if ($request->filled('status')) {

            switch ($request->status) {

                // VOID
                case 'Void':

                    $query->where(
                        'warranties.status',
                        'Void'
                    );

                    break;


                // EXPIRED
                case 'Expired':

                    $query->where(
                        'warranties.status',
                        '!=',
                        'Void'
                    )
                        ->whereDate(
                            'warranties.tanggal_expired',
                            '<',
                            now()->toDateString()
                        );

                    break;


                // ACTIVE
                case 'Active':

                    $query->where(
                        'warranties.status',
                        '!=',
                        'Void'
                    )
                        ->whereDate(
                            'warranties.tanggal_expired',
                            '>=',
                            now()->toDateString()
                        );

                    break;


                // CLAIM
                case 'Claim':

                    $query->where(
                        'warranties.status',
                        'Claim'
                    );

                    break;
            }
        }


        return DataTables::of($query)

            ->addColumn('status_view', function ($row) {

                if ($row->status == 'Void') {

                    return 'Void';
                }

                if ($row->tanggal_expired < now()->toDateString()) {

                    return 'Expired';
                }

                return 'Active';
            })

            ->toJson();
    }

    public function store(Request $request)
    {
        $type = WarrantyType::where('code', $request->warranty_type)->where('is_active', true)->first();
        if (!$type) return response()->json(['message' => 'Jenis warranty tidak valid.'], 422);

        $rules = [
            'id_customer' => 'required|exists:customers,id_customer', 'warranty_type' => 'required',
            'tanggal_pasang' => 'required|date', 'items' => 'required|array|min:1',
            'items.*.id_product' => 'required|exists:products,id_product', 'items.*.status' => 'nullable|in:Active,Claim,Expired,Void',
        ];
        if (in_array($type->code, ['CAR', 'PPF'])) $rules += ['no_polisi' => 'required', 'merk_mobil' => 'required', 'tipe_mobil' => 'required', 'warna_mobil' => 'required', 'tahun_mobil' => 'required'];
        if ($type->code === 'BUILDING') $rules += ['nama_bangunan' => 'required', 'alamat_bangunan' => 'required', 'items.*.area_pekerjaan' => 'required', 'items.*.panjang' => 'required|numeric|gt:0', 'items.*.lebar' => 'required|numeric|gt:0', 'items.*.jumlah' => 'required|integer|gt:0'];
        if ($type->code === 'CAR') $rules['items.*.posisi_kaca'] = 'required';
        if ($type->code === 'PPF') $rules['items.*.area_pekerjaan'] = 'required';
        $validated = $request->validate($rules);
        $productIds = collect($validated['items'])->pluck('id_product')->unique();
        if (Product::whereIn('id_product', $productIds)->where('status', 'enabled')->count() !== $productIds->count()) {
            return response()->json(['message' => 'Product yang dipilih harus berstatus aktif.'], 422);
        }

        $result = DB::transaction(function () use ($request, $type, $validated) {
            $items = $validated['items'];
            $firstProduct = Product::findOrFail($items[0]['id_product']);
            $expiries = [];
            foreach ($items as $item) {
                $product = Product::findOrFail($item['id_product']);
                $expiries[] = date('Y-m-d', strtotime($request->tanggal_pasang . ' +' . $product->masa_garansi_bulan . ' months'));
            }
            $kode = $this->generateKodeWarranty();
            $header = [
                'kode_warranty' => $kode, 'pin_warranty' => (string) random_int(100000, 999999),
                'id_customer' => $validated['id_customer'], 'id_warranty_type' => $type->id,
                // Legacy columns are deliberately kept populated for existing reports/detail screens.
                'id_product' => $firstProduct->id_product, 'no_invoice' => $request->no_invoice,
                'tanggal_pasang' => $request->tanggal_pasang, 'tanggal_expired' => max($expiries),
                'installer' => $request->installer, 'catatan' => $request->catatan, 'status' => 'Active',
                'user_id' => Auth::user()->user_id, 'created_at' => now(), 'updated_at' => now(),
            ];
            if (in_array($type->code, ['CAR', 'PPF'])) {
                $header += ['no_polisi' => strtoupper($request->no_polisi), 'merk_mobil' => $request->merk_mobil, 'tipe_mobil' => $request->tipe_mobil, 'warna_mobil' => $request->warna_mobil, 'tahun_mobil' => $request->tahun_mobil];
            }
            $id = DB::table('warranties')->insertGetId($header, 'id_warranty');
            foreach ($items as $index => $item) {
                $product = Product::findOrFail($item['id_product']);
                $base = ['id_warranty' => $id, 'id_product' => $product->id_product, 'tanggal_pasang' => $request->tanggal_pasang, 'tanggal_expired' => $expiries[$index], 'status' => $item['status'] ?? 'Active', 'catatan' => $item['catatan'] ?? null];
                if ($type->code === 'CAR') WarrantyVehicleItem::create($base + ['posisi_kaca' => $item['posisi_kaca']]);
                if ($type->code === 'PPF') WarrantyPpfItem::create($base + ['area_pekerjaan' => $item['area_pekerjaan']]);
                if ($type->code === 'BUILDING') {
                    $luas = round((float) $item['panjang'] * (float) $item['lebar'], 2);
                    WarrantyBuildingItem::create($base + ['area_pekerjaan' => $item['area_pekerjaan'], 'panjang' => $item['panjang'], 'lebar' => $item['lebar'], 'jumlah' => $item['jumlah'], 'luas_per_item' => $luas, 'total_luas' => round($luas * (int) $item['jumlah'], 2)]);
                }
            }
            $identity = ['id_warranty' => $id, 'no_polisi' => strtoupper($request->no_polisi), 'merk_mobil' => $request->merk_mobil, 'tipe_mobil' => $request->tipe_mobil, 'warna_mobil' => $request->warna_mobil, 'tahun_mobil' => $request->tahun_mobil];
            if ($type->code === 'CAR') WarrantyVehicle::create($identity);
            if ($type->code === 'PPF') WarrantyPpf::create($identity);
            if ($type->code === 'BUILDING') WarrantyBuilding::create(['id_warranty' => $id, 'nama_bangunan' => $request->nama_bangunan, 'alamat' => $request->alamat_bangunan]);
            return [$kode, $header['pin_warranty']];
        });

        [$kode] = $result;
        $folder = public_path('qrcode'); if (!is_dir($folder)) mkdir($folder, 0755, true);
        $url = route('warranty.digital', ['kode' => $kode]); $fileName = $kode . '.svg';
        file_put_contents($folder . DIRECTORY_SEPARATOR . $fileName, QrCode::size(300)->margin(2)->generate($url));
        Warranty::where('kode_warranty', $kode)->update(['qr_code' => $fileName]);
        return response()->json(['success' => true, 'kode_warranty' => $kode, 'qr_code' => asset('qrcode/' . $fileName), 'url' => $url]);
    }


    public function show($id_warranty)
    {
        if (empty(Auth::user()->username)) {
            return redirect()->to('/login');
        }

        $warranty = Warranty::with([
            'customer:id_customer,nama_customer',
            'warrantyType:id,code,name',
            'user:user_id,name',
            'vehicle.items.product:id_product,nama_produk,masa_garansi_bulan',
            'building.items.product:id_product,nama_produk,masa_garansi_bulan',
            'ppf.items.product:id_product,nama_produk,masa_garansi_bulan',
            'assetVehicle:id_vehicle,no_polisi,merk,model,warna,tahun',
            'assetBuilding:id_building,nama_bangunan,alamat',
            'warrantyItems.product:id_product,nama_produk',
            'order.activeInvoice',
            'order.technician',
        ])->findOrFail($id_warranty);

        $type = optional($warranty->warrantyType)->code ?: 'CAR';
        $isTransactionWarranty = (bool) $warranty->id_order;
        $identity = $isTransactionWarranty ? ($type === 'BUILDING' ? $warranty->assetBuilding : $warranty->assetVehicle) : ($type === 'BUILDING' ? $warranty->building : ($type === 'PPF' ? $warranty->ppf : $warranty->vehicle));
        $items = $isTransactionWarranty ? $warranty->warrantyItems : ($identity ? $identity->items : collect());

        return response()->json([
            'id_warranty' => $warranty->id_warranty,
            'kode_warranty' => $warranty->kode_warranty,
            'pin_warranty' => $warranty->pin_warranty,
            'id_customer' => $warranty->id_customer,
            'nama_customer' => optional($warranty->customer)->nama_customer,
            'id_warranty_type' => $warranty->id_warranty_type,
            'warranty_type' => $type,
            'warranty_type_name' => optional($warranty->warrantyType)->name,
            'no_invoice' => $warranty->no_invoice ?: optional(optional($warranty->order)->activeInvoice)->invoice_number,
            'tanggal_pasang' => $warranty->tanggal_pasang,
            'tanggal_expired' => $warranty->tanggal_expired,
            'installer' => $warranty->installer ?: optional(optional($warranty->order)->technician)->name,
            'catatan' => $warranty->catatan ?: optional($warranty->order)->notes,
            'status' => $this->effectiveStatus($warranty->status, $warranty->tanggal_expired),
            'created_by' => optional($warranty->user)->name,
            'vehicle' => $type === 'CAR' ? ($isTransactionWarranty ? ['no_polisi' => optional($identity)->no_polisi, 'merk_mobil' => optional($identity)->merk, 'tipe_mobil' => optional($identity)->model, 'warna_mobil' => optional($identity)->warna, 'tahun_mobil' => optional($identity)->tahun] : $identity) : null,
            'building' => $type === 'BUILDING' ? $identity : null,
            'ppf' => $type === 'PPF' ? $identity : null,
            'items' => $items->map(function ($item) use ($warranty, $isTransactionWarranty) {
                $item->status = $this->effectiveStatus($warranty->status, $item->tanggal_expired, $item->status);
                if ($isTransactionWarranty) { $item->area_pekerjaan = $item->area; $item->posisi_kaca = $item->area; }
                return $item;
            })->values(),
        ]);
    }



    public function update(Request $request)
    {
        $warranty = Warranty::with('warrantyType')->findOrFail($request->id_warranty);
        if ($warranty->status === 'Void') return response()->json(['message' => 'Warranty Void tidak dapat diubah.'], 422);

        $type = optional($warranty->warrantyType)->code ?: 'CAR';
        $rules = ['id_customer_detail' => 'required|exists:customers,id_customer', 'tanggal_pasang_detail' => 'required|date', 'items' => 'required|array|min:1', 'items.*.id_product' => 'required|exists:products,id_product', 'items.*.status' => 'nullable|in:Active,Claim'];
        if (in_array($type, ['CAR', 'PPF'])) $rules += ['no_polisi_detail' => 'required', 'merk_mobil_detail' => 'required', 'tipe_mobil_detail' => 'required', 'warna_mobil_detail' => 'required', 'tahun_mobil_detail' => 'required'];
        if ($type === 'BUILDING') $rules += ['nama_bangunan_detail' => 'required', 'alamat_bangunan_detail' => 'required', 'items.*.area_pekerjaan' => 'required', 'items.*.panjang' => 'required|numeric|gt:0', 'items.*.lebar' => 'required|numeric|gt:0', 'items.*.jumlah' => 'required|integer|gt:0'];
        if ($type === 'CAR') $rules['items.*.posisi_kaca'] = 'required';
        if ($type === 'PPF') $rules['items.*.area_pekerjaan'] = 'required';
        $validated = $request->validate($rules);
        $productIds = collect($validated['items'])->pluck('id_product')->unique();
        if (Product::whereIn('id_product', $productIds)->where('status', 'enabled')->count() !== $productIds->count()) {
            return response()->json(['message' => 'Product yang dipilih harus berstatus aktif.'], 422);
        }

        if ($request->filled('no_invoice_detail') && Warranty::where('no_invoice', $request->no_invoice_detail)->where('id_warranty', '!=', $warranty->id_warranty)->exists()) return response('must_unique', 422);

        DB::transaction(function () use ($request, $validated, $warranty, $type) {
            $expiries = [];
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['id_product']);
                $expiries[] = date('Y-m-d', strtotime($request->tanggal_pasang_detail . ' +' . $product->masa_garansi_bulan . ' months'));
            }
            $header = ['id_customer' => $validated['id_customer_detail'], 'id_product' => $validated['items'][0]['id_product'], 'no_invoice' => $request->no_invoice_detail, 'tanggal_pasang' => $request->tanggal_pasang_detail, 'tanggal_expired' => max($expiries), 'installer' => $request->installer_detail, 'catatan' => $request->catatan_detail];
            if (in_array($type, ['CAR', 'PPF'])) $header += ['no_polisi' => strtoupper($request->no_polisi_detail), 'merk_mobil' => $request->merk_mobil_detail, 'tipe_mobil' => $request->tipe_mobil_detail, 'warna_mobil' => $request->warna_mobil_detail, 'tahun_mobil' => $request->tahun_mobil_detail];
            $warranty->update($header);
            $identity = $type === 'BUILDING' ? $warranty->building() : ($type === 'PPF' ? $warranty->ppf() : $warranty->vehicle());
            $identity->firstOrFail()->items()->delete();
            foreach ($validated['items'] as $index => $item) {
                $base = ['id_warranty' => $warranty->id_warranty, 'id_product' => $item['id_product'], 'tanggal_pasang' => $request->tanggal_pasang_detail, 'tanggal_expired' => $expiries[$index], 'status' => $item['status'] ?? 'Active'];
                if ($type === 'CAR') WarrantyVehicleItem::create($base + ['posisi_kaca' => $item['posisi_kaca']]);
                if ($type === 'PPF') WarrantyPpfItem::create($base + ['area_pekerjaan' => $item['area_pekerjaan']]);
                if ($type === 'BUILDING') { $luas = round($item['panjang'] * $item['lebar'], 2); WarrantyBuildingItem::create($base + ['area_pekerjaan' => $item['area_pekerjaan'], 'panjang' => $item['panjang'], 'lebar' => $item['lebar'], 'jumlah' => $item['jumlah'], 'luas_per_item' => $luas, 'total_luas' => round($luas * $item['jumlah'], 2)]); }
            }
            if ($type === 'BUILDING') $identity->update(['nama_bangunan' => $request->nama_bangunan_detail, 'alamat' => $request->alamat_bangunan_detail]);
            else $identity->update(['no_polisi' => strtoupper($request->no_polisi_detail), 'merk_mobil' => $request->merk_mobil_detail, 'tipe_mobil' => $request->tipe_mobil_detail, 'warna_mobil' => $request->warna_mobil_detail, 'tahun_mobil' => $request->tahun_mobil_detail]);
        });

        return response()->json(['success' => true]);
    }

    private function effectiveStatus($headerStatus, $expiredAt, $itemStatus = null)
    {
        if ($headerStatus === 'Void') return 'Void';
        if ($itemStatus === 'Claim' || $headerStatus === 'Claim') return 'Claim';
        return $expiredAt < now()->toDateString() ? 'Expired' : 'Active';
    }


    public function destroy(Request $request)
    {

        $id = request('id_warranty');

        $warranty = Warranty::find($id);

        $warranty->delete();
    }

    private function generateKodeWarranty()
    {
        $last = Warranty::latest('id_warranty')->first();

        if ($last) {
            $nomor = $last->id_warranty + 1;
        } else {
            $nomor = 1;
        }

        return 'WR' . date('Y') . str_pad($nomor, 3, '0', STR_PAD_LEFT);
    }

    public function digitalWarranty($kode)
    {
        $warranty = Warranty::with(['customer', 'product', 'warrantyType', 'warrantyItems', 'assetVehicle', 'assetBuilding', 'vehicle.items.product', 'building.items.product', 'ppf.items.product'])
            ->where('kode_warranty', $kode)
            ->first();

        if (!$warranty) {
            abort(404);
        }

        // Jika Warranty sudah Void
        if ($warranty->status == 'Void') {
            abort(404);
        }

        if (session('digital_warranty_verified.' . $warranty->id_warranty) !== true) {
            return view('warranty.pin', compact('warranty'));
        }

        return view('warranty.digital', compact('warranty'));
    }

    public function verifyDigitalPin(Request $request, $kode)
    {
        $request->validate(['pin_warranty' => 'required|digits:6']);
        $warranty = Warranty::where('kode_warranty', $kode)->where('status', '!=', 'Void')->firstOrFail();
        if (!hash_equals((string) $warranty->pin_warranty, (string) $request->pin_warranty)) {
            return back()->withErrors(['pin_warranty' => 'PIN warranty tidak sesuai.'])->withInput();
        }
        $request->session()->put('digital_warranty_verified.' . $warranty->id_warranty, true);
        return redirect()->route('warranty.digital', $kode);
    }

    public function downloadPdf($kode)
    {
        $warranty = Warranty::with('product')
            ->where('kode_warranty', $kode)
            ->firstOrFail();

        // $pdf = Pdf::loadView('warranty.pdf', compact('warranty'));

        // return $pdf->download($warranty->kode_warranty . '.pdf');

        $pdf = Pdf::loadView('warranty.pdf', compact('warranty'));

        return $pdf->stream();
    }

    public function print($kode)
    {
        $warranty = Warranty::with(['customer', 'product'])
            ->where('kode_warranty', $kode)
            ->firstOrFail();

        return view('warranty.print', compact('warranty'));
    }

    public function void(Request $request)
    {
        $warranty = Warranty::find($request->id_warranty_void);

        if (!$warranty) {

            return response('NOT_FOUND');
        }

        // Sudah pernah di-void
        if ($warranty->status == 'Void') {

            return response('ALREADY_VOID');
        }

        $warranty->status = 'Void';
        $warranty->save();

        return response('SUCCESS');
    }
}
