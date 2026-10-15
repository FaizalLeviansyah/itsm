<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    protected $fillable = [
        'priority',
        'response_time_minutes',
        'resolution_time_minutes',
    ];
}
