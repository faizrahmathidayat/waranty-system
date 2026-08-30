<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WarrantyBuildingItem extends Model { protected $fillable = ['id_warranty','id_product','area_pekerjaan','panjang','lebar','jumlah','luas_per_item','total_luas','tanggal_pasang','tanggal_expired','status','catatan']; public function warranty() { return $this->belongsTo(Warranty::class, 'id_warranty'); } public function product() { return $this->belongsTo(Product::class, 'id_product', 'id_product'); } }
