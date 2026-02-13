<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'requested_by_employeeid',
        'uid',
        'tech_employeeid',
        'employee_name',
        'topic',
        'description',
        'it_tech_name',
        'status',
        'date_resolved',
        'date',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'date_resolved' => 'datetime',
        'date' => 'datetime',
    ];
}
