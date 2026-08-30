<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Technician extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_technician';
    protected $fillable = ['code', 'name', 'phone', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'id_technician', 'id_technician');
    }
}
