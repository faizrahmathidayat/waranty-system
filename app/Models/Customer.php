<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    use HasFactory;
    protected $primaryKey = 'id_customer';
    protected $table = 'customers';
    protected $guarded = [];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'id_customer', 'id_customer');
    }

    public function buildings()
    {
        return $this->hasMany(Building::class, 'id_customer', 'id_customer');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'id_customer', 'id_customer');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'id_customer', 'id_customer');
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class, 'id_customer', 'id_customer');
    }

    public static function cek_duplikat_email($request)
    {

        $cek_duplikat_email = Customer::where('email',  $request['email'])->get()->count();
        return $cek_duplikat_email;
    }

    public static function data_post_insert($request)
    {

        $status    = 'enabled';

        $request = array(
            'nama_customer'         => request('nama_customer'),
            'no_hp'                 => request('no_hp'),
            'email'                 => request('email'),
            'alamat'                => request('alamat'),
            'status'                => $status
        );


        return $request;
    }

    public static function cek_duplikat_email_update($request)
    {
        $datalama = Customer::find($request['id_customer']);

        if ($datalama['email'] == $request['email_detail']) {
            return '0';
        } else {

            $cek_duplikat_email_update = Customer::where('email',  $request['email_detail'])->get()->count();
            return $cek_duplikat_email_update;
        }
    }

    public static function data_post_update($request)
    {
        $request = array(
            'nama_customer' => request('nama_customer_detail'),
            'no_hp'         => request('no_hp_detail'),
            'email'         => request('email_detail'),
            'alamat'        => request('alamat_detail'),
            'status'        => request('status')
        );

        return $request;
    }
}
