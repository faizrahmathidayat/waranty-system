<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WarrantyType extends Model { protected $fillable = ['code','name','is_active','created_by','updated_by']; public function warranties() { return $this->hasMany(Warranty::class, 'id_warranty_type'); } }
