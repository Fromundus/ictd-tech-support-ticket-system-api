<?php

namespace App\Http\Controllers\Api\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function all(Request $request){
        $employees = Employee::whereHas('setup', function($q){
            $q->where('activation_status', 'Activate');
        })->get([
            'employeeid',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
        ]);

        return response()->json($employees);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        return Employee::query()
            ->when($query, function ($q) use ($query) {
                $q->where('lastname', 'like', "%{$query}%")
                ->orWhere('firstname', 'like', "%{$query}%")
                ->orWhere('middlename', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get([
                'employeeid',
                'firstname',
                'middlename',
                'lastname',
                'suffix'
            ]);
    }

    public function index(Request $request)
    {
        $search  = $request->query('search');
        $perPage = $request->query('per_page', 10);

        $query = Employee::with('setup');

        if ($search) {
            // Split search by spaces to allow full name search
            $searchTerms = explode(' ', $search);

            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($q2) use ($term) {
                        $q2->where('employeeid', 'like', "%{$term}%")
                            ->orWhere('firstname', 'like', "%{$term}%")
                            ->orWhere('lastname', 'like', "%{$term}%")
                            ->orWhere('middlename', 'like', "%{$term}%")
                            ->orWhere('gender', 'like', "%{$term}%")
                            ->orWhereHas('setup', function ($q3) use ($term) {
                                $q3->where('dept_code', 'like', "%{$term}%")
                                ->orWhere('emp_status', 'like', "%{$term}%");
                            });
                    });
                }
            });
        }

        $employees = $query->orderByDesc('employeeid')->paginate($perPage);

        return response()->json([
            'employees' => [
                'data' => $employees->items(),
                'current_page' => $employees->currentPage(),
                'last_page'    => $employees->lastPage(),
                'per_page'     => $employees->perPage(),
                'total'        => $employees->total(),
            ]
        ]);
    }

    public function show($employeeid){
        $employee = Employee::with(['setup', 'devices.owner'])->where('employeeid', $employeeid)->first();

        return response()->json($employee);
    }
}
