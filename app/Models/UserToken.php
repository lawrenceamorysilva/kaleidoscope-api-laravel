<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    // Table name (if it doesn’t follow Laravel plural convention)
    protected $table = 'user_tokens';

    // Primary key type
    protected $keyType = 'int';
    public $incrementing = true;

    // Timestamps
    public $timestamps = false; // since you have created_at manually, no updated_at

    // Mass assignable fields
    protected $fillable = [
        'user_id',
        'token',
        'portal',
        'expires_at',
        'created_at',
    ];

    // If you want Carbon casting for dates
    protected $dates = [
        'expires_at',
        'created_at',
    ];
}
