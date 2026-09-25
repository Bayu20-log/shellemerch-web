<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'pin_size_id', 'product_id', 'size_name', 'unit_price',
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Item pin custom (desain + ukuran) vs item produk katalog (tanpa desain/ukuran).
    public function isCustomPin(): bool
    {
        return $this->pin_size_id !== null || $this->design_path !== null;
    }

    public function getSubtotalAttribute(): int
    {
        return $this->unit_price * $this->quantity;
    }
}
