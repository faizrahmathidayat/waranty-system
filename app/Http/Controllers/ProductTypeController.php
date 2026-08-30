<?php
namespace App\Http\Controllers;
use App\Models\ProductType;
use Illuminate\Http\Request;
class ProductTypeController extends MasterDataController {
    protected string $model = ProductType::class; protected string $primaryKey = 'id_product_type'; protected string $routePrefix = 'product-type'; protected string $title = 'Product Type';
    protected array $rules = ['code'=>'required|string|max:30|unique:product_types,code','name'=>'required|string|max:100','description'=>'nullable|string','order_category'=>'required|in:AUTOMOTIVE,BUILDING','is_active'=>'nullable|boolean'];
    protected array $fields = [['name'=>'code','label'=>'Code','type'=>'text'],['name'=>'name','label'=>'Name','type'=>'text'],['name'=>'order_category','label'=>'Kategori Order','type'=>'order_category'],['name'=>'description','label'=>'Description','type'=>'textarea'],['name'=>'is_active','label'=>'Status','type'=>'active']];
    protected array $columns = [['data'=>'code','name'=>'product_types.code','label'=>'Code'],['data'=>'name','name'=>'product_types.name','label'=>'Name'],['data'=>'order_category','name'=>'product_types.order_category','label'=>'Kategori Order'],['data'=>'description','name'=>'product_types.description','label'=>'Description'],['data'=>'is_active','name'=>'product_types.is_active','label'=>'Status']];
    protected function beforeSave(array &$data, Request $request): void { $data['is_active'] = $request->boolean('is_active'); if ($request->isMethod('post') && $request->routeIs('product-type.update')) unset($data['code']); }
    public function update(Request $request) { $this->rules['code']='required|string|max:30|unique:product_types,code,'.$request->id_product_type.',id_product_type'; return parent::update($request); }
}
