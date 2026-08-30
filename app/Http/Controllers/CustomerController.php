<?php

namespace App\Http\Controllers;


use App\Models\Customer;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $data = Customer::data_post_insert($request->all());

        if ($duplikat_email > 0) {
            echo "must_unique";
        } else {
            echo "";
            Customer::insert($data);
        }
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

        $data = Customer::data_post_update($request);

        if ($duplikat_nama_email_update > 0) {
            echo "must_unique";
        } else {

            echo "";
            Customer::where('id_customer', $id)->update($data);
        }
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
