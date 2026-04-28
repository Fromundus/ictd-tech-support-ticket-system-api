<?php

namespace App\Models\Payroll;

use App\Models\Device;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'employee';

    protected $primaryKey = 'employeeid';

    public $incrementing = false;

    protected $fillable = [
        'allow_bid',
        'birthdate',
        'birthplace',
        'blood_type',
        'created',
        'createdby',
        'employeeid',
        'firstname',
        'gender',
        'lastname',
        'maritalstatus',
        'middlename',
        'modified',
        'modifiedby',
        'nationality',
        'photoid',
        'photopath',
        'profilepic',
        'suffix',
    ];

    public $timestamps = false;

    public function setup()
    {
        return $this->hasOne(EmploymentSetup::class, 'employeeid', 'employeeid')
                    ->where('isServiceRec', 0)
                    ->orderByDesc('employment_code'); // latest employment_code
    }

    public function devices(){
        return $this->hasMany(Device::class, 'mr_employeeid', 'employeeid');
    }
}
