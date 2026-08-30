<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_invoice_item';

    protected $fillable = ['id_invoice', 'id_order_detail', 'product_name', 'variant_name', 'treatment_name', 'area', 'quantity', 'unit', 'panjang', 'lebar', 'total_luas', 'unit_price', 'discount', 'subtotal'];

    protected $casts = ['quantity' => 'decimal:2', 'panjang' => 'decimal:2', 'lebar' => 'decimal:2', 'total_luas' => 'decimal:2', 'unit_price' => 'decimal:2', 'discount' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class, 'id_invoice', 'id_invoice'); }
    public function orderDetail(): BelongsTo { return $this->belongsTo(OrderDetail::class, 'id_order_detail', 'id_order_detail'); }
}
