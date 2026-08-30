<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Login;
use App\Models\Product;
use App\Models\Warranty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{

    public function index(Request $request)
    {

        $count_warranty = Warranty::count();
        $count_product = Product::count();
        $count_customer = Customer::count();
        $count_user = Login::whereIn('role', ['Staff', 'Admin'])->where('status', '!=', 'deleted')->count();

        if (empty(Auth::User()->username)) {
            return redirect()->to('/login');
        } else {
            return view(
                'dashboard.index',
                [
                    'title'     => 'Home',
                    'navbar'    => '',
                ],
                compact('count_warranty', 'count_product', 'count_customer', 'count_user')
            );
        }
    }

    public function chartWarranty(Request $request)
    {

        $tahun = $request->tahun ?? date('Y');

        $chart = Warranty::select(
            DB::raw('MONTH(tanggal_pasang) as bulan'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('tanggal_pasang', $tahun)
            ->groupBy(DB::raw('MONTH(tanggal_pasang)'))
            ->pluck('total', 'bulan');

        $label = [];
        $data = [];

        for ($i = 1; $i <= 12; $i++) {

            $label[] = Carbon::create()
                ->month($i)
                ->translatedFormat('M');

            $data[] = $chart[$i] ?? 0;
        }

        return response()->json([
            'label' => $label,
            'data'  => $data,
            'total' => array_sum($data)
        ]);
    }

    public function chartStatusWarranty()
    {
        if (Schema::hasTable('warranty_items')) {
            $items = $this->warrantyItemsQuery();
            $active = DB::query()->fromSub($items, 'items')->where('header_status', '!=', 'Void')->where('item_status', '!=', 'Void')->whereDate('tanggal_expired', '>=', today())->count();
            $expired = DB::query()->fromSub($this->warrantyItemsQuery(), 'items')->where('header_status', '!=', 'Void')->whereDate('tanggal_expired', '<', today())->count();
        } else {
        $active = Warranty::whereDate(
            'tanggal_expired',
            '>=',
            date('Y-m-d')
        )->count();

        $expired = Warranty::whereDate(
            'tanggal_expired',
            '<',
            date('Y-m-d')
        )->count();
        }

        $total = $active + $expired;

        return response()->json([

            'label' => ['Active', 'Expired'],

            'data' => [$active, $expired],

            'active' => $active,

            'expired' => $expired,

            'total' => $total,

            'active_percent' => $total > 0
                ? round(($active / $total) * 100, 1)
                : 0,

            'expired_percent' => $total > 0
                ? round(($expired / $total) * 100, 1)
                : 0

        ]);
    }

    public function latestWarranty()
    {
        if (Schema::hasTable('warranty_items')) {
            return response()->json(
                DB::table('warranty_items as items')
                    ->join('warranties', 'warranties.id_warranty', '=', 'items.id_warranty')
                    ->join('customers', 'customers.id_customer', '=', 'warranties.id_customer')
                    ->leftJoin('products', 'products.id_product', '=', 'items.id_product')
                    ->select(
                        'warranties.id_warranty',
                        'warranties.kode_warranty',
                        'customers.nama_customer',
                        DB::raw('COALESCE(items.product_name_snapshot, products.nama_produk) as nama_produk'),
                        'items.tanggal_pasang',
                        'items.tanggal_expired'
                    )
                    ->where('warranties.status', '!=', 'Void')
                    ->where('items.status', '!=', 'Void')
                    ->orderByDesc('items.id_warranty_item')
                    ->limit(5)
                    ->get()
            );
        }

        $data = Warranty::select(
            'warranties.id_warranty',
            'warranties.kode_warranty',
            'customers.nama_customer',
            'products.nama_produk',
            'warranties.tanggal_pasang',
            'warranties.tanggal_expired'
        )
            ->join('customers', 'customers.id_customer', '=', 'warranties.id_customer')
            ->join('products', 'products.id_product', '=', 'warranties.id_product')
            ->orderBy('warranties.id_warranty', 'desc')
            ->limit(5)
            ->get();

        return response()->json($data);
    }

    public function warrantyExpiredSoon()
    {
        $today = Carbon::today();

        $next7Days = Carbon::today()->addDays(7);

        if (Schema::hasTable('warranty_items')) {
            $data = DB::query()->fromSub($this->warrantyItemsQuery(), 'items')
                ->join('customers', 'customers.id_customer', '=', 'items.id_customer')
                ->join('products', 'products.id_product', '=', 'items.id_product')
                ->select('items.kode_warranty', 'customers.nama_customer', 'products.nama_produk', 'items.tanggal_expired')
                ->where('items.header_status', '!=', 'Void')->where('items.item_status', '!=', 'Void')
                ->whereBetween('items.tanggal_expired', [$today, $next7Days])->orderBy('items.tanggal_expired')->limit(5)->get();
        } else {
        $data = Warranty::select(
            'warranties.kode_warranty',
            'customers.nama_customer',
            'products.nama_produk',
            'warranties.tanggal_expired'
        )
            ->join('customers', 'customers.id_customer', '=', 'warranties.id_customer')
            ->join('products', 'products.id_product', '=', 'warranties.id_product')
            ->whereBetween('warranties.tanggal_expired', [$today, $next7Days])
            ->orderBy('warranties.tanggal_expired', 'asc')
            ->limit(5)
            ->get();
        }

        $data->map(function ($item) {

            $item->sisa_hari = Carbon::today()->diffInDays(
                Carbon::parse($item->tanggal_expired),
                false
            );

            return $item;
        });

        return response()->json($data);
    }

    public function productTerlaris()
    {
        if (Schema::hasTable('warranty_items')) {
            return response()->json(DB::query()->fromSub($this->warrantyItemsQuery(), 'items')
                ->join('products', 'products.id_product', '=', 'items.id_product')
                ->select('products.nama_produk', DB::raw('COUNT(*) as total'))
                ->where('items.header_status', '!=', 'Void')->where('items.item_status', '!=', 'Void')
                ->groupBy('products.id_product', 'products.nama_produk')->orderByDesc('total')->limit(5)->get());
        }
        $data = Warranty::select(
            'products.nama_produk',
            DB::raw('COUNT(warranties.id_warranty) as total')
        )
            ->join(
                'products',
                'products.id_product',
                '=',
                'warranties.id_product'
            )
            ->where('warranties.status', '!=', 'Void')
            ->groupBy(
                'products.id_product',
                'products.nama_produk'
            )
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return response()->json($data);
    }

    private function warrantyItemsQuery()
    {
        if (Schema::hasTable('warranty_items')) {
            return DB::table('warranty_items as i')
                ->join('warranties as w', 'w.id_warranty', '=', 'i.id_warranty')
                ->select('i.id_product', 'i.tanggal_expired', 'i.status as item_status', 'w.status as header_status', 'w.kode_warranty', 'w.id_customer');
        }

        $vehicle = DB::table('warranty_vehicle_items as i')->join('warranties as w', 'w.id_warranty', '=', 'i.id_warranty')
            ->select('i.id_product', 'i.tanggal_expired', 'i.status as item_status', 'w.status as header_status', 'w.kode_warranty', 'w.id_customer');
        $building = DB::table('warranty_building_items as i')->join('warranties as w', 'w.id_warranty', '=', 'i.id_warranty')
            ->select('i.id_product', 'i.tanggal_expired', 'i.status as item_status', 'w.status as header_status', 'w.kode_warranty', 'w.id_customer');
        $ppf = DB::table('warranty_ppf_items as i')->join('warranties as w', 'w.id_warranty', '=', 'i.id_warranty')
            ->select('i.id_product', 'i.tanggal_expired', 'i.status as item_status', 'w.status as header_status', 'w.kode_warranty', 'w.id_customer');
        return $vehicle->unionAll($building)->unionAll($ppf);
    }
}
