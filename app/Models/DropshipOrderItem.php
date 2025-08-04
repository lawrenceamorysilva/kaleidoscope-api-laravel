<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropshipOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'qty',
        'price',
        'shipping_weight',
    ];

    public function order()
    {
        return $this->belongsTo(DropshipOrder::class);
    }
}

