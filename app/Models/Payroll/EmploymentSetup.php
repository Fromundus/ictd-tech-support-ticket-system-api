<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;

class EmploymentSetup extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'employment_setup';

    protected $primaryKey = 'employment_code';

    // If your PK is not auto-incrementing
    public $incrementing = false;

    protected $fillable = [
        'employment_code',
        'employeeid',
        'temp_position',
        'desig_position',
        'plantilla_id',
        'dept_code',
        'div_code',
        'sec_code',
        'empstatID',
        'emp_status',
        'WithPlantilla',
        'WithLeave',
        'RestWithPay',
        'PayType',
        'WithUndertime',
        'EmpStatTag',
        'service_start',
        'service_end',
        'Permanency',
        'rankname',
        'stepname',
        'cola',
        'basic_salary',
        'remarks',
        'lock_id',
        'created',
        'createdby',
        'modified',
        'modifiedby',
        'activation_status',
        'activated',
        'activatedby',
        'deactivated',
        'deactivatedby',
        'org',
        'desigStat',
        'actStatus',
        'isServiceRec',
    ];


    public $timestamps = false;

    public function employee(){
        return $this->belongsTo(Employee::class, 'employeeid', 'employeeid');
    }
}
