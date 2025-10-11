<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalContent extends Model
{
    protected $fillable = ['key', 'title', 'content', 'updated_by'];
}
