<?php

namespace App\Models;

use App\Support\HasAvailability;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasAvailability;

    protected $fillable = ['image', 'name', 'price', 'description', 'availability'];
}