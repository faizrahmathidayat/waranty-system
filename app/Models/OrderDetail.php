<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderDetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_order_detail';

    protected $fillable = ['id_order', 'id_treatment', 'id_product', 'id_product_variant', 'area', 'item_type', 'quantity', 'unit', 'panjang', 'lebar', 'luas_per_item', 'total_luas', 'unit_price', 'discount', 'subtotal', 'warranty_eligible', 'warranty_months_snapshot', 'tanggal_expired_snapshot', 'service_status', 'completed_at', 'installer_id', 'product_name_snapshot', 'variant_name_snapshot', 'treatment_name_snapshot', 'notes'];

    protected $casts = ['quantity' => 'decimal:2', 'panjang' => 'decimal:2', 'lebar' => 'decimal:2', 'luas_per_item' => 'decimal:2', 'total_luas' => 'decimal:2', 'unit_price' => 'decimal:2', 'discount' => 'decimal:2', 'subtotal' => 'decimal:2', 'warranty_eligible' => 'boolean', 'warranty_months_snapshot' => 'integer', 'tanggal_expired_snapshot' => 'date', 'completed_at' => 'datetime'];

    public function order(): BelongsTo { return $this->belongsTo(Order::class, 'id_order', 'id_order'); }
    public function treatment(): BelongsTo { return $this->belongsTo(Treatment::class, 'id_treatment', 'id_treatment'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class, 'id_product', 'id_product'); }
    public function productVariant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'id_product_variant', 'id_product_variant'); }
    public function invoiceItem(): HasOne { return $this->hasOne(InvoiceItem::class, 'id_order_detail', 'id_order_detail'); }
    public function warrantyItems(): HasMany { return $this->hasMany(WarrantyItem::class, 'id_order_detail', 'id_order_detail'); }
}
