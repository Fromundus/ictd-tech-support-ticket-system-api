<?php

namespace App\Models;

use App\Models\Payroll\Employee;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $connection = 'mysql';
    protected $table = 'devices';

    protected $fillable = [
        'mr_employeeid',
        'mr_employee_name',
        'user_employeeid',
        'user_employee_name',
        'name',
        'type',
        'custom_type',
        'brand',
        'model',
        'serial_number',
        'status',
        'location',
        'description',

        'processor',
        'ram',
        'system_type',
        'operating_system',
        'storage',
        'mac_address',

        'created_by_employeeid',
        'updated_by_employeeid',

        'purchased_at',
    ];

    public function owner(){
        return $this->belongsTo(Employee::class, 'mr_employeeid', 'employeeid');
    }

    public function user(){
        return $this->belongsTo(Employee::class, 'user_employeeid', 'employeeid');
    }

    public function histories(){
        return $this->hasMany(DeviceHistory::class);
    }

    public function tickets(){
        return $this->hasMany(Ticket::class);
    }
}
