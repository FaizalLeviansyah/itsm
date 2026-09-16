<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    protected $fillable = ['name', 'slug', 'color', 'icon', 'sla_hours', 'response_hours', 'sort_order'];
}
