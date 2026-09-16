<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $connection = 'master_employee';
    protected $table = 'tbl_employee';
    protected $primaryKey = 'employee_id';
    public $timestamps = false;

    protected $guarded = [];
}
