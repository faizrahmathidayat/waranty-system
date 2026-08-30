<?php

namespace App\Http\Controllers;


use App\Models\Customer;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;


class CustomerController extends Controller
{
    public function index()
    {
        if (empty(Auth::User()->username)) {
            return redirect()->to('/login');
        }

        return view('customer.index', [
            'title'     => 'Customer',
            'navbar'    => 'Customer'
        ]);
    }

    public function data()
    {
        if (empty(Auth::user()->username)) {
            return redirect()->to('/login');
        }

        return DataTables::of(Customer::query()->orderByDesc('id_customer'))->toJson();
    }

    public function store(Request $request)
    {
        $duplikat_email = Customer::cek_duplikat_email($request->all());
        if ($duplikat_email > 0) {
            echo "must_unique";
            return;
        }

        $vehicles = $this->validatedVehicleRows($request->input('vehicles', []));
        $buildings = $this->validatedBuildingRows($request->input('buildings', []));

        DB::transaction(function () use ($request, $vehicles, $buildings) {
            $data = Customer::data_post_insert($request->all());
            $customer = Customer::create($data);
            foreach ($vehicles as $vehicle) {
                $customer->vehicles()->create($vehicle + ['status' => 'active', 'created_by' => Auth::id()]);
            }
            foreach ($buildings as $building) {
                $customer->buildings()->create($building + ['status' => 'active', 'created_by' => Auth::id()]);
            }
        });

        echo "";
    }

    public function show($id_customer)
    {

        if (empty(Auth::User()->username)) {
            return redirect()->to('/login');
        }

        $show = Customer::findOrFail($id_customer);
        echo json_encode($show);
    }

    public function update(Request $request)
    {
        $id = request('id_customer');

        $duplikat_nama_email_update = Customer::cek_duplikat_email_update($request);
        if ($duplikat_nama_email_update > 0) {
            echo "must_unique";
            return;
        }

        $vehicles = $this->validatedVehicleRows($request->input('vehicles', []));
        $buildings = $this->validatedBuildingRows($request->input('buildings', []));

        DB::transaction(function () use ($request, $id, $vehicles, $buildings) {
            $data = Customer::data_post_update($request);
            $customer = Customer::where('id_customer', $id)->firstOrFail();
            $customer->update($data);
            foreach ($vehicles as $vehicle) {
                $customer->vehicles()->create($vehicle + ['status' => 'active', 'created_by' => Auth::id()]);
            }
            foreach ($buildings as $building) {
                $customer->buildings()->create($building + ['status' => 'active', 'created_by' => Auth::id()]);
            }
        });

        echo "";
    }

    /**
     * Filter out blank rows, then validate the rest against the same rules
     * VehicleController uses for its own master-data form.
     */
    private function validatedVehicleRows(array $rows): array
    {
        $result = [];
        foreach ($rows as $index => $row) {
            if (collect($row)->every(function ($value) { return $value === null || $value === ''; })) {
                continue;
            }
            $result[] = Validator::make($row, [
                'no_polisi' => 'required|string|max:20',
                'merk' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'warna' => 'required|string|max:50',
                'tahun' => 'required|integer|min:1900|max:2100',
            ], [
                'required' => ':attribute harus diisi terlebih dahulu.',
                'integer' => ':attribute harus berupa angka bulat.',
                'max' => ':attribute terlalu panjang.',
                'min' => ':attribute tidak valid.',
            ], [
                'no_polisi' => 'No Polisi (Vehicle baris '.($index + 1).')',
                'merk' => 'Merk (Vehicle baris '.($index + 1).')',
                'model' => 'Model (Vehicle baris '.($index + 1).')',
                'warna' => 'Warna (Vehicle baris '.($index + 1).')',
                'tahun' => 'Tahun (Vehicle baris '.($index + 1).')',
            ])->validate();
        }
        return $result;
    }

    /**
     * Filter out blank rows, then validate the rest against the same rules
     * BuildingController uses for its own master-data form.
     */
    private function validatedBuildingRows(array $rows): array
    {
        $result = [];
        foreach ($rows as $index => $row) {
            if (collect($row)->every(function ($value) { return $value === null || $value === ''; })) {
                continue;
            }
            $result[] = Validator::make($row, [
                'nama_bangunan' => 'required|string|max:150',
                'alamat' => 'required|string',
            ], [
                'required' => ':attribute harus diisi terlebih dahulu.',
                'max' => ':attribute terlalu panjang.',
            ], [
                'nama_bangunan' => 'Nama Bangunan (Building baris '.($index + 1).')',
                'alamat' => 'Alamat (Building baris '.($index + 1).')',
            ])->validate();
        }
        return $result;
    }

    public function destroy(Request $request)
    {
        $id = request('id_customer');
        $customer = Customer::find($id);
        $cek_customer_use = Warranty::where('id_customer', $id)->count();

        if ($customer['status'] == 'enabled') {
            echo "Y";
        } else if ($cek_customer_use > '0') {
            echo "USED";
        } else {

            $customer->delete();
        }
    }
}
