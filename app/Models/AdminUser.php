<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;


class AdminUser extends Authenticatable
{
    protected $table = 'admin_users';
    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];
    protected $hidden = ['password', 'remember_token'];

    // JWT methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
