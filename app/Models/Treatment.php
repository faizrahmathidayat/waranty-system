<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Treatment extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_treatment';

    protected $fillable = ['code', 'name', 'description', 'order_category', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function orderDetails(): HasMany { return $this->hasMany(OrderDetail::class, 'id_treatment', 'id_treatment'); }
    public function warrantyItems(): HasMany { return $this->hasMany(WarrantyItem::class, 'id_treatment', 'id_treatment'); }
}
