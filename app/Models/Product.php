<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    use HasFactory;

    protected $primaryKey = 'id_product';
    protected $table = 'products';

    protected $casts = [
        'is_warranty_eligible' => 'boolean',
        'harga_default' => 'decimal:2',
    ];

    public function productType()
    {
        return $this->belongsTo(ProductType::class, 'id_product_type', 'id_product_type');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'id_product', 'id_product');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'id_product', 'id_product');
    }

    public function warrantyItems()
    {
        return $this->hasMany(WarrantyItem::class, 'id_product', 'id_product');
    }


    protected function cek_duplikat_produk($request)
    {

        $cek_duplikat_produk = Product::where('nama_produk', $request['nama_produk'])
            ->where('brand', $request['brand'])
            ->count();

        return $cek_duplikat_produk;
    }


    protected function data_post_insert($request)
    {
        $status = 'enabled';
        $request = array(

            'nama_produk'          => request('nama_produk'),
            'brand'                => request('brand'),
            'jenis'                => request('jenis'),
            'id_product_type'      => request('id_product_type'),
            'kode_produk'          => request('kode_produk'),
            'harga_default'        => request('harga_default'),
            'is_warranty_eligible' => request()->boolean('is_warranty_eligible', true),
            'status'                => $status,
            'keterangan'           => request('keterangan')

        );

        return $request;
    }


    protected function cek_duplikat_produk_update($request)
    {

        $datalama = Product::find($request['id_product']);

        if (
            $datalama['nama_produk'] == $request['nama_produk_detail']
            &&
            $datalama['brand'] == $request['brand_detail']
        ) {

            return '0';
        } else {

            $cek_duplikat_produk_update = Product::where('nama_produk', $request['nama_produk_detail'])
                ->where('brand', $request['brand_detail'])
                ->count();

            return $cek_duplikat_produk_update;
        }
    }


    protected function data_post_update($request)
    {

        $request = array(

            'nama_produk'          => request('nama_produk_detail'),
            'brand'                => request('brand_detail'),
            'jenis'                => request('jenis_detail'),
            'id_product_type'      => request('id_product_type_detail'),
            'kode_produk'          => request('kode_produk_detail'),
            'harga_default'        => request('harga_default_detail'),
            'is_warranty_eligible' => request()->boolean('is_warranty_eligible_detail', true),
            'keterangan'           => request('keterangan_detail'),
            'status'               => request('status')

        );

        return $request;
    }
}
