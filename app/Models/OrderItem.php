<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'pin_size_id', 'size_name', 'unit_price',
        'quantity', 'design_path', 'notes',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function pinSize()
    {
        return $this->belongsTo(PinSize::class);
    }

    public function getSubtotalAttribute(): int
    {
        return $this->unit_price * $this->quantity;
    }
}
