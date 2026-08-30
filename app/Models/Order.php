<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_order';

    protected $fillable = ['order_number', 'id_customer', 'order_type', 'id_vehicle', 'id_building', 'id_technician', 'order_date', 'status', 'service_completed_at', 'subtotal', 'discount', 'grand_total', 'notes', 'created_by', 'updated_by'];

    protected $casts = ['order_date' => 'date', 'service_completed_at' => 'datetime', 'subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'grand_total' => 'decimal:2'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class, 'id_customer', 'id_customer'); }
    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class, 'id_vehicle', 'id_vehicle'); }
    public function building(): BelongsTo { return $this->belongsTo(Building::class, 'id_building', 'id_building'); }
    public function technician(): BelongsTo { return $this->belongsTo(Technician::class, 'id_technician', 'id_technician'); }
    public function details(): HasMany { return $this->hasMany(OrderDetail::class, 'id_order', 'id_order'); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class, 'id_order', 'id_order'); }
    public function activeInvoice(): HasOne { return $this->hasOne(Invoice::class, 'id_order', 'id_order')->where('status', '!=', 'CANCELLED')->latestOfMany('id_invoice'); }
    public function warranties(): HasMany { return $this->hasMany(Warranty::class, 'id_order', 'id_order'); }
}
