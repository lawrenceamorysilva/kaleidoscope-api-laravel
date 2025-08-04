<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropshipOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'po_number',
        'delivery_instructions',
        'first_name',
        'last_name',
        'business_name',
        'shipping_address_line1',
        'shipping_address_line2',
        'suburb',
        'state',
        'postcode',
        'phone',
        'authority_to_leave',
        'product_total',
        'shipping_total',
        'dropship_fee',
        'min_order_fee',
        'grand_total',
        'selected_courier',
        'available_shipping_options',
    ];

    protected $casts = [
        'available_shipping_options' => 'array',
    ];


    public function items()
    {
        return $this->hasMany(DropshipOrderItem::class);
    }
}

