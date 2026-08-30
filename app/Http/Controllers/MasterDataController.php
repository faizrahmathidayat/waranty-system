<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

abstract class MasterDataController extends Controller
{
    protected string $model;
    protected string $primaryKey;
    protected string $routePrefix;
    protected string $title;
    protected array $rules = [];
    protected array $fields = [];
    protected array $columns = [];

    protected function ensureAuthenticated()
    {
        if (empty(Auth::user()->username)) {
            abort(403);
        }
    }

    public function index()
    {
        $this->ensureAuthenticated();
        return view('master_data.index', [
            'title' => $this->title,
            'navbar' => $this->title,
            'master' => $this->configuration(),
            'options' => $this->options(),
        ]);
    }

    public function data()
    {
        $this->ensureAuthenticated();
        return DataTables::of($this->query())->toJson();
    }

    public function show($id)
    {
        $this->ensureAuthenticated();
        return response()->json(($this->model)::findOrFail($id));
    }

    public function store(Request $request)
    {
        $this->ensureAuthenticated();
        $this->prepareStatusRequest($request);
        $data = $request->validate($this->rules, $this->validationMessages(), $this->validationAttributes());
        $this->normalizeStatus($data, $request);
        $this->beforeSave($data, $request);
        ($this->model)::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request)
    {
        $this->ensureAuthenticated();
        $this->prepareStatusRequest($request);
        $data = $request->validate($this->rules, $this->validationMessages(), $this->validationAttributes());
        $this->normalizeStatus($data, $request);
        $this->beforeSave($data, $request);
        ($this->model)::findOrFail($request->input($this->primaryKey))->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $this->ensureAuthenticated();
        $record = ($this->model)::findOrFail($request->input($this->primaryKey));
        $this->deactivate($record);
        return response()->json(['success' => true]);
    }

    protected function query() { return ($this->model)::query()->orderByDesc($this->primaryKey); }
    protected function options(): array { return []; }
    protected function beforeSave(array &$data, Request $request): void { }
    protected function deactivate($record): void { $record->update(['is_active' => false]); }
    protected function validationMessages(): array { return ['required' => ':attribute harus diisi terlebih dahulu.', 'numeric' => ':attribute harus berupa angka.', 'integer' => ':attribute harus berupa angka bulat.', 'exists' => ':attribute yang dipilih tidak valid.', 'unique' => ':attribute sudah digunakan.', 'min' => ':attribute tidak valid.', 'max' => ':attribute terlalu panjang.', 'in' => ':attribute tidak valid.']; }
    protected function validationAttributes(): array { return collect($this->fields)->mapWithKeys(fn ($field) => [$field['name'] => $field['label']])->all(); }
    protected function prepareStatusRequest(Request $request): void { if (collect($this->fields)->contains(fn ($field) => $field['type'] === 'status')) $request->merge(['status' => $request->boolean('status') ? 'active' : 'inactive']); }
    protected function normalizeStatus(array &$data, Request $request): void { if (collect($this->fields)->contains(fn ($field) => $field['type'] === 'status')) $data['status'] = $request->input('status'); }
    protected function configuration(): array { return ['route' => $this->routePrefix, 'primaryKey' => $this->primaryKey, 'title' => $this->title, 'fields' => $this->fields, 'columns' => $this->columns]; }
}
