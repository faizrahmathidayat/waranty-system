<?php
namespace App\Http\Controllers;
use App\Models\Treatment;
use Illuminate\Http\Request;
class TreatmentController extends MasterDataController {
    protected string $model = Treatment::class; protected string $primaryKey = 'id_treatment'; protected string $routePrefix = 'treatment'; protected string $title = 'Treatment';
    protected array $rules = ['code'=>'required|string|max:30|unique:treatments,code','name'=>'required|string|max:100','description'=>'nullable|string','order_category'=>'required|in:AUTOMOTIVE,BUILDING','is_active'=>'nullable|boolean'];
    protected array $fields = [['name'=>'code','label'=>'Code','type'=>'text'],['name'=>'name','label'=>'Name','type'=>'text'],['name'=>'order_category','label'=>'Kategori Order','type'=>'order_category'],['name'=>'description','label'=>'Description','type'=>'textarea'],['name'=>'is_active','label'=>'Status','type'=>'active']];
    protected array $columns = [['data'=>'code','name'=>'treatments.code','label'=>'Code'],['data'=>'name','name'=>'treatments.name','label'=>'Name'],['data'=>'order_category','name'=>'treatments.order_category','label'=>'Kategori Order'],['data'=>'description','name'=>'treatments.description','label'=>'Description'],['data'=>'is_active','name'=>'treatments.is_active','label'=>'Status']];
    protected function beforeSave(array &$data, Request $request): void { $data['is_active'] = $request->boolean('is_active', true); }
    public function update(Request $request) { $this->rules['code']='required|string|max:30|unique:treatments,code,'.$request->id_treatment.',id_treatment'; return parent::update($request); }
}
