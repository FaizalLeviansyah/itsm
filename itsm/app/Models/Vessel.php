<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vessel extends Model
{
    protected $connection = 'master_ship';
    protected $table = 'vessel';
    public $timestamps = false;

    protected $fillable = [];
    protected $guarded = [];
}
