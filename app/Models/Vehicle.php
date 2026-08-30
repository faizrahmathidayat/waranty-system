<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_vehicle';

    protected $fillable = ['id_customer', 'no_polisi', 'merk', 'model', 'warna', 'tahun', 'status', 'created_by', 'updated_by'];

    protected $casts = ['tahun' => 'integer'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class, 'id_customer', 'id_customer'); }
    public function orders(): HasMany { return $this->hasMany(Order::class, 'id_vehicle', 'id_vehicle'); }
    public function warranties(): HasMany { return $this->hasMany(Warranty::class, 'id_vehicle', 'id_vehicle'); }
}
