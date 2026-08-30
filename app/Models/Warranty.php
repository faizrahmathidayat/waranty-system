<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Warranty extends Model
{
    use HasFactory;

    protected $table = 'warranties';

    protected $primaryKey = 'id_warranty';
    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | CEK DUPLIKAT INVOICE
    |--------------------------------------------------------------------------
    */

    public static function cek_duplikat_invoice($request)
    {
        return Warranty::where('no_invoice', $request['no_invoice'])
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    public static function data_post_insert($request)
    {

        $status = 'Active';

        $data = [

            'kode_warranty'    => request('kode_warranty'),

            'qr_code'          => request('qr_code'),

            'id_customer'      => request('id_customer'),

            'id_product'       => request('id_product'),

            'user_id'          => Auth::user()->user_id,

            'no_invoice'       => request('no_invoice'),

            'no_polisi'        => strtoupper(request('no_polisi')),

            'merk_mobil'       => request('merk_mobil'),

            'tipe_mobil'       => request('tipe_mobil'),

            'warna_mobil'      => request('warna_mobil'),

            'tahun_mobil'      => request('tahun_mobil'),

            'tanggal_pasang'   => request('tanggal_pasang'),

            'tanggal_expired'  => request('tanggal_expired'),

            'installer'        => request('installer'),

            'status'           => $status,

            'catatan'          => request('catatan')

        ];

        return $data;
    }


    /*
    |--------------------------------------------------------------------------
    | CEK DUPLIKAT UPDATE
    |--------------------------------------------------------------------------
    */

    public static function cek_duplikat_invoice_update($request)
    {

        $lama = Warranty::find($request['id_warranty']);

        if ($lama->no_invoice == $request['no_invoice_detail']) {

            return 0;
        }

        return Warranty::where('no_invoice', $request['no_invoice_detail'])
            ->count();
    }



    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public static function data_post_update($request)
    {

        $data = [

            'id_customer'      => request('id_customer_detail'),

            'id_product'       => request('id_product_detail'),

            'no_invoice'       => request('no_invoice_detail'),

            'no_polisi'        => strtoupper(request('no_polisi_detail')),

            'merk_mobil'       => request('merk_mobil_detail'),

            'tipe_mobil'       => request('tipe_mobil_detail'),

            'warna_mobil'      => request('warna_mobil_detail'),

            'tahun_mobil'      => request('tahun_mobil_detail'),

            'tanggal_pasang'   => request('tanggal_pasang_detail'),

            'tanggal_expired'  => request('tanggal_expired_detail'),

            'installer'        => request('installer_detail'),

            'catatan'          => request('catatan_detail')

        ];

        return $data;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_product');
    }

    public function warrantyType(): BelongsTo
    {
        return $this->belongsTo(WarrantyType::class, 'id_warranty_type');
    }

    public function vehicle(): HasOne
    {
        return $this->hasOne(WarrantyVehicle::class, 'id_warranty', 'id_warranty');
    }

    public function building(): HasOne
    {
        return $this->hasOne(WarrantyBuilding::class, 'id_warranty', 'id_warranty');
    }

    public function ppf(): HasOne
    {
        return $this->hasOne(WarrantyPpf::class, 'id_warranty', 'id_warranty');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'id_invoice', 'id_invoice');
    }

    // Legacy vehicle(), building(), and ppf() remain warranty snapshots.
    public function assetVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'id_vehicle', 'id_vehicle');
    }

    public function assetBuilding(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'id_building', 'id_building');
    }

    public function warrantyItems(): HasMany
    {
        return $this->hasMany(WarrantyItem::class, 'id_warranty', 'id_warranty');
    }

    public function user()
    {
        return $this->belongsTo(Login::class, 'user_id', 'user_id');
    }
}
