<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request)
    {
        // Check if user has permission to view employees
        $this->authorize('view employees');

        // Get query parameters
        $search = $request->input('search');
        $departmentId = $request->input('department');
        $perPage = $request->input('per_page', 10);

        // Ensure this queries the database
        $employeesQuery = Employee::with(['user', 'department', 'position'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($departmentId, function ($query, $departmentId) {
                return $query->where('department_id', $departmentId);
            })
            ->orderBy('first_name');

        // Paginate the results
        $employees = $employeesQuery->paginate($perPage)
            ->through(function ($employee) {
                return [
                    'id' => $employee->id,
                    'employee_id' => $employee->employee_id,
                    'full_name' => $employee->first_name . ' ' . $employee->last_name,
                    'email' => $employee->user->email,
                    'department' => $employee->department->name,
                    'department_id' => $employee->department_id,
                    'position' => $employee->position->title,
                    'position_id' => $employee->position_id,
                    'profile_picture' => $employee->profile_picture,
                    'hire_date' => $employee->hire_date,
                    'employment_status' => $employee->employment_status,
                ];
            });

        // Get all departments for the filter
        $departments = Department::select('id', 'name')->orderBy('name')->get();
        
        // Add "All Departments" option
        $allDepartments = [['id' => 0, 'name' => 'All Departments']];
        $departmentOptions = array_merge($allDepartments, $departments->toArray());

        return Inertia::render('Employees/index', [
            'employees' => $employees,
            'departments' => $departmentOptions,
            'filters' => [
                'search' => $search,
                'department' => $departmentId,
            ],
        ]);
    }

    // Other controller methods (show, create, store, edit, update, destroy)...
}