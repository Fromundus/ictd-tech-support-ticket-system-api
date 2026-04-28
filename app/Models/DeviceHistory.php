<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceHistory extends Model
{
    protected $fillable = [
        'device_id',
        'ticket_id',
        'date_requested',
        'issue',
        'action',
        'date_performed',
        'performed_by_employeeid',
        'performed_by_name',
        'remarks',
    ];

    public function device(){
        return $this->belongsTo(Device::class);
    }
}
