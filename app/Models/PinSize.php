<?php

namespace App\Models;

use App\Support\HasAvailability;
use Illuminate\Database\Eloquent\Model;

class PinSize extends Model
{
    use HasAvailability;

    protected $fillable = ['name', 'price', 'is_active', 'availability', 'stock'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
