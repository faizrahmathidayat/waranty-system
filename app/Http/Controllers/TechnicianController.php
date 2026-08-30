<?php

namespace App\Http\Controllers;

use App\Models\Technician;
use Illuminate\Http\Request;

class TechnicianController extends MasterDataController
{
    protected string $model = Technician::class;
    protected string $primaryKey = 'id_technician';
    protected string $routePrefix = 'technician';
    protected string $title = 'Teknisi';
    protected array $rules = ['code' => 'required|string|max:30|unique:technicians,code', 'name' => 'required|string|max:100', 'phone' => 'nullable|string|max:30', 'is_active' => 'nullable|boolean'];
    protected array $fields = [['name' => 'code', 'label' => 'Kode', 'type' => 'text'], ['name' => 'name', 'label' => 'Nama Teknisi', 'type' => 'text'], ['name' => 'phone', 'label' => 'No. Telepon', 'type' => 'text'], ['name' => 'is_active', 'label' => 'Status', 'type' => 'active']];
    protected array $columns = [['data' => 'code', 'name' => 'technicians.code', 'label' => 'Kode'], ['data' => 'name', 'name' => 'technicians.name', 'label' => 'Nama Teknisi'], ['data' => 'phone', 'name' => 'technicians.phone', 'label' => 'No. Telepon'], ['data' => 'is_active', 'name' => 'technicians.is_active', 'label' => 'Status']];

    protected function beforeSave(array &$data, Request $request): void { $data['is_active'] = $request->boolean('is_active', true); }
    public function update(Request $request) { $this->rules['code'] = 'required|string|max:30|unique:technicians,code,'.$request->id_technician.',id_technician'; return parent::update($request); }
}
