<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_payment';

    protected $fillable = ['id_invoice', 'payment_date', 'payment_type', 'amount', 'payment_method', 'reference_number', 'notes', 'status', 'voided_at', 'voided_by', 'void_reason', 'created_by'];

    protected $casts = ['payment_date' => 'datetime', 'voided_at' => 'datetime', 'amount' => 'decimal:2'];

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class, 'id_invoice', 'id_invoice'); }
}
