<?php

// app/Models/NetoProduct.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetoProduct extends Model
{
    protected $fillable = [
        'sku', 'neto_id', 'name', 'brand', 'approved', 'stock_status', 'dropship', 'dropship_price',
        'qty', 'shipping_weight', 'shipping_length', 'shipping_width', 'shipping_height',
        'images', 'status', 'status_reason',
    ];

    protected $casts = [
        'images' => 'array',
        'approved' => 'boolean',
    ];
}
