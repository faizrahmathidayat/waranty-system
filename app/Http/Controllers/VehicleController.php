<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
class VehicleController extends MasterDataController {
    protected string $model = Vehicle::class; protected string $primaryKey = 'id_vehicle'; protected string $routePrefix = 'vehicle'; protected string $title = 'Vehicle';
    protected array $rules = ['id_customer'=>'required|exists:customers,id_customer','no_polisi'=>'required|string|max:20','merk'=>'required|string|max:100','model'=>'required|string|max:100','warna'=>'required|string|max:50','tahun'=>'required|integer|min:1900|max:'.'2100','status'=>'required|in:active,inactive'];
    protected array $fields = [['name'=>'id_customer','label'=>'Customer','type'=>'select','options'=>'customers'],['name'=>'no_polisi','label'=>'No Polisi','type'=>'text'],['name'=>'merk','label'=>'Merk','type'=>'text'],['name'=>'model','label'=>'Model/Tipe','type'=>'text'],['name'=>'warna','label'=>'Warna','type'=>'text'],['name'=>'tahun','label'=>'Tahun','type'=>'number'],['name'=>'status','label'=>'Status','type'=>'status']];
    protected array $columns = [['data'=>'nama_customer','name'=>'customers.nama_customer','label'=>'Customer'],['data'=>'no_polisi','name'=>'vehicles.no_polisi','label'=>'No Polisi'],['data'=>'merk','name'=>'vehicles.merk','label'=>'Merk'],['data'=>'model','name'=>'vehicles.model','label'=>'Model'],['data'=>'warna','name'=>'vehicles.warna','label'=>'Warna'],['data'=>'tahun','name'=>'vehicles.tahun','label'=>'Tahun'],['data'=>'status','name'=>'vehicles.status','label'=>'Status']];
    protected function query() { return Vehicle::query()->join('customers','customers.id_customer','=','vehicles.id_customer')->select('vehicles.*','customers.nama_customer')->orderByDesc('id_vehicle'); }
    protected function options(): array { return ['customers'=>Customer::where('status','enabled')->orderBy('nama_customer')->get(['id_customer','nama_customer'])]; }
    protected function deactivate($record): void { $record->update(['status'=>'inactive']); }
}
