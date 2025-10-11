<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalSetting extends Model
{
    protected $fillable = ['key', 'value', 'description', 'updated_by'];
}
