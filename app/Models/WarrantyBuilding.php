<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WarrantyBuilding extends Model { protected $fillable = ['id_warranty','nama_bangunan','alamat']; public function warranty() { return $this->belongsTo(Warranty::class, 'id_warranty'); } public function items() { return $this->hasMany(WarrantyBuildingItem::class, 'id_warranty', 'id_warranty'); } }
