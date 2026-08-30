<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_invoice';

    protected $fillable = ['invoice_number', 'id_order', 'id_customer', 'invoice_date', 'due_date', 'subtotal', 'discount', 'tax_amount', 'grand_total', 'paid_amount', 'outstanding_amount', 'status', 'notes', 'created_by', 'updated_by'];

    protected $casts = ['invoice_date' => 'date', 'due_date' => 'date', 'subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'tax_amount' => 'decimal:2', 'grand_total' => 'decimal:2', 'paid_amount' => 'decimal:2', 'outstanding_amount' => 'decimal:2'];

    public function order(): BelongsTo { return $this->belongsTo(Order::class, 'id_order', 'id_order'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class, 'id_customer', 'id_customer'); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class, 'id_invoice', 'id_invoice'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class, 'id_invoice', 'id_invoice'); }
    public function warranties(): HasMany { return $this->hasMany(Warranty::class, 'id_invoice', 'id_invoice'); }
}
