<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_product_variant';

    protected $fillable = ['id_product', 'code', 'name', 'value', 'unit', 'harga_tambahan', 'is_active'];

    protected $casts = ['harga_tambahan' => 'decimal:2', 'is_active' => 'boolean'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class, 'id_product', 'id_product'); }
    public function orderDetails(): HasMany { return $this->hasMany(OrderDetail::class, 'id_product_variant', 'id_product_variant'); }
    public function warrantyItems(): HasMany { return $this->hasMany(WarrantyItem::class, 'id_product_variant', 'id_product_variant'); }
}
