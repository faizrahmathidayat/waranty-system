<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_building';

    protected $fillable = ['id_customer', 'nama_bangunan', 'alamat', 'status', 'created_by', 'updated_by'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class, 'id_customer', 'id_customer'); }
    public function orders(): HasMany { return $this->hasMany(Order::class, 'id_building', 'id_building'); }
    public function warranties(): HasMany { return $this->hasMany(Warranty::class, 'id_building', 'id_building'); }
}
