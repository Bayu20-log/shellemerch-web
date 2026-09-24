<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusLog extends Model
{
    protected $fillable = ['order_id', 'from_status', 'to_status', 'user_id', 'note'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return $this->from_status === null
            ? 'Pesanan dibuat'
            : (Order::STATUS_LABELS[$this->to_status] ?? $this->to_status);
    }
}
