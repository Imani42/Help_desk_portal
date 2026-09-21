<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagerReportComment extends Model
{
    protected $fillable = ['manager_id', 'year', 'comment'];
}
