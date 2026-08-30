<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WarrantyVehicle extends Model { protected $fillable = ['id_warranty','no_polisi','merk_mobil','tipe_mobil','warna_mobil','tahun_mobil']; public function warranty() { return $this->belongsTo(Warranty::class, 'id_warranty'); } public function items() { return $this->hasMany(WarrantyVehicleItem::class, 'id_warranty', 'id_warranty'); } }
