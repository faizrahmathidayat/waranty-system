<?php
namespace App\Http\Controllers;
use App\Models\Building;
use App\Models\Customer;
class BuildingController extends MasterDataController {
    protected string $model = Building::class; protected string $primaryKey = 'id_building'; protected string $routePrefix = 'building'; protected string $title = 'Building';
    protected array $rules = ['id_customer'=>'required|exists:customers,id_customer','nama_bangunan'=>'required|string|max:150','alamat'=>'required|string','status'=>'nullable|in:active,inactive'];
    protected array $fields = [['name'=>'id_customer','label'=>'Customer','type'=>'select','options'=>'customers'],['name'=>'nama_bangunan','label'=>'Nama Bangunan','type'=>'text'],['name'=>'alamat','label'=>'Alamat','type'=>'textarea'],['name'=>'status','label'=>'Status','type'=>'status']];
    protected array $columns = [['data'=>'nama_customer','name'=>'customers.nama_customer','label'=>'Customer'],['data'=>'nama_bangunan','name'=>'buildings.nama_bangunan','label'=>'Nama Bangunan'],['data'=>'alamat','name'=>'buildings.alamat','label'=>'Alamat'],['data'=>'status','name'=>'buildings.status','label'=>'Status']];
    protected function query() { return Building::query()->join('customers','customers.id_customer','=','buildings.id_customer')->select('buildings.*','customers.nama_customer')->orderByDesc('id_building'); }
    protected function options(): array { return ['customers'=>Customer::where('status','enabled')->orderBy('nama_customer')->get(['id_customer','nama_customer'])]; }
    protected function deactivate($record): void { $record->update(['status'=>'inactive']); }
}
