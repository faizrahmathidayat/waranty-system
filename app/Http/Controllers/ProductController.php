<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warranty;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index()
    {
        if (empty(Auth::User()->username)) {
            return redirect()->to('/login');
        }

        return view('product.index', [
            'title'     => 'Product',
            'navbar'    => 'Product',
            'productTypes' => ProductType::where('is_active', true)->orderBy('name')->get()
        ]);
    }

    public function data()
    {
        if (empty(Auth::user()->username)) {
            return redirect()->to('/login');
        }

        $product = DataTables::of(Product::query()
            ->leftJoin('product_types', 'product_types.id_product_type', '=', 'products.id_product_type')
            ->select('products.*', 'product_types.name as product_type_name')
            ->orderBy('products.id_product', 'desc'))->toJson();

        return $product;
    }

    public function store(Request $request)
    {
        $duplikat_produk = Product::cek_duplikat_produk($request->all());

        $data = Product::data_post_insert($request->all());

        if ($duplikat_produk > 0) {

            echo "must_unique";
        } else {

            echo "";

            Product::insert($data);
        }
    }

    public function show($id_product)
    {
        if (empty(Auth::User()->username)) {
            return redirect()->to('/login');
        }

        $show = Product::findOrFail($id_product);

        echo json_encode($show);
    }

    public function update(Request $request)
    {
        $id = request('id_product');

        $duplikat_produk = Product::cek_duplikat_produk_update($request);

        $data = Product::data_post_update($request);

        if ($duplikat_produk > 0) {

            echo "must_unique";
        } else {

            echo "";

            Product::where('id_product', $id)
                ->update($data);
        }
    }

    public function destroy(Request $request)
    {


        $id = request('id_product');

        $product = Product::find($id);

        $cek_product_use = Warranty::where('id_product', $id)->count();

        if ($cek_product_use > 0) {

            echo "USED";
        } else {

            $product->delete();
        }
    }
}
