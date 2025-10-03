<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'employee_name',
        'topic',
        'description',
        'it_tech_name',
        'status',
        'date_resolved',
    ];
}
