<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
class ProductVariantController extends MasterDataController {
    protected string $model = ProductVariant::class; protected string $primaryKey = 'id_product_variant'; protected string $routePrefix = 'product-variant'; protected string $title = 'Product Variant';
    protected array $rules = ['id_product'=>'required|exists:products,id_product','name'=>'required|string|max:100','value'=>'nullable|string|max:50','is_active'=>'nullable|boolean'];
    protected array $fields = [['name'=>'id_product','label'=>'Product','type'=>'select','options'=>'products'],['name'=>'name','label'=>'Name','type'=>'text'],['name'=>'value','label'=>'Kegelapan','type'=>'text'],['name'=>'is_active','label'=>'Status','type'=>'active']];
    protected array $columns = [['data'=>'nama_produk','name'=>'products.nama_produk','label'=>'Product'],['data'=>'name','name'=>'product_variants.name','label'=>'Name'],['data'=>'value','name'=>'product_variants.value','label'=>'Kegelapan'],['data'=>'is_active','name'=>'product_variants.is_active','label'=>'Status']];
    protected function query() { return ProductVariant::query()->join('products','products.id_product','=','product_variants.id_product')->select('product_variants.*','products.nama_produk')->orderByDesc('id_product_variant'); }
    protected function options(): array { return ['products'=>Product::where('status','enabled')->orderBy('nama_produk')->get(['id_product','nama_produk'])]; }
    protected function beforeSave(array &$data, Request $request): void { $data['is_active']=$request->boolean('is_active',true); }
}
