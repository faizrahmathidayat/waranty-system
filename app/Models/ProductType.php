<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductType extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_product_type';

    protected $fillable = ['code', 'name', 'description', 'order_category', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function products(): HasMany { return $this->hasMany(Product::class, 'id_product_type', 'id_product_type'); }
}
