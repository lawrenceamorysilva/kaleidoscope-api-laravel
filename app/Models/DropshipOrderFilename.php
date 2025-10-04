<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropshipOrderFilename extends Model
{
    use HasFactory;

    protected $table = 'dropship_order_filename';

    protected $fillable = [
        'admin_users_id',
        'filename',
    ];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class, 'admin_users_id');
    }

    public function orders()
    {
        return $this->hasMany(DropshipOrder::class, 'dropship_order_filename_id');
    }
}
