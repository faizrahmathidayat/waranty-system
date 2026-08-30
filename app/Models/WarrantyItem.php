<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_warranty_item';

    protected $fillable = ['id_warranty', 'id_order_detail', 'id_treatment', 'id_product', 'id_product_variant', 'item_type', 'area', 'quantity', 'unit', 'panjang', 'lebar', 'luas_per_item', 'total_luas', 'tanggal_pasang', 'tanggal_expired', 'status', 'product_name_snapshot', 'variant_name_snapshot', 'treatment_name_snapshot', 'catatan'];

    protected $casts = ['quantity' => 'decimal:2', 'panjang' => 'decimal:2', 'lebar' => 'decimal:2', 'luas_per_item' => 'decimal:2', 'total_luas' => 'decimal:2', 'tanggal_pasang' => 'date', 'tanggal_expired' => 'date'];

    public function warranty(): BelongsTo { return $this->belongsTo(Warranty::class, 'id_warranty', 'id_warranty'); }
    public function orderDetail(): BelongsTo { return $this->belongsTo(OrderDetail::class, 'id_order_detail', 'id_order_detail'); }
    public function treatment(): BelongsTo { return $this->belongsTo(Treatment::class, 'id_treatment', 'id_treatment'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class, 'id_product', 'id_product'); }
    public function productVariant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'id_product_variant', 'id_product_variant'); }
}
