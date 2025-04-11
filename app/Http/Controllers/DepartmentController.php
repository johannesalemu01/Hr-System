<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     */
    public function index(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user has admin privileges
        $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager']);
        
        // Get query parameters for filtering
        $search = $request->query('search');
        
        // Base query for departments
        $query = Department::with(['manager.user']);
        
        // Apply search filter
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        
        // Get departments with employee count
        $departments = $query->withCount('employees')
                            ->orderBy('name')
                            ->paginate(10);
        
        // Transform departments for frontend
        $departmentsData = $departments->map(function ($department) {
            return [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'description' => $department->description,
                'manager' => $department->manager ? [
                    'id' => $department->manager->id,
                    'name' => $department->manager->first_name . ' ' . $department->manager->last_name,
                    'avatar' => $department->manager->profile_picture ?? '/placeholder.svg?height=40&width=40',
                ] : null,
                'employees_count' => $department->employees_count,
                'created_at' => $department->created_at,
            ];
        });
        
        // Get department statistics
        $totalDepartments = Department::count();
        $totalEmployees = Employee::count();
        $avgEmployeesPerDepartment = $totalDepartments > 0 ? round($totalEmployees / $totalDepartments, 1) : 0;
        $largestDepartment = Department::withCount('employees')
                                ->orderBy('employees_count', 'desc')
                                ->first();
        
        return Inertia::render('Departments/index', [
            'departments' => $departmentsData,
            'filters' => [
                'search' => $search,
            ],
            'stats' => [
                'totalDepartments' => $totalDepartments,
                'totalEmployees' => $totalEmployees,
                'avgEmployeesPerDepartment' => $avgEmployeesPerDepartment,
                'largestDepartment' => $largestDepartment ? [
                    'name' => $largestDepartment->name,
                    'count' => $largestDepartment->employees_count,
                ] : null,
            ],
            'isAdmin' => $isAdmin,
            'pagination' => [
                'links' => $departments->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $departments->currentPage(),
                    'from' => $departments->firstItem(),
                    'last_page' => $departments->lastPage(),
                    'path' => $departments->path(),
                    'per_page' => $departments->perPage(),
                    'to' => $departments->lastItem(),
                    'total' => $departments->total(),
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user has admin privileges
        if (!$user->hasRole(['super-admin', 'hr-admin'])) {
            return redirect()->route('departments.index')->with('error', 'You do not have permission to create departments.');
        }
        
        // Get managers (employees who are managers)
        $managers = Employee::whereHas('user', function ($query) {
            $query->role('manager');
        })->get()->map(function ($manager) {
            return [
                'id' => $manager->id,
                'name' => $manager->first_name . ' ' . $manager->last_name,
                'employee_id' => $manager->employee_id,
            ];
        });
        
        return Inertia::render('Departments/Create', [
            'managers' => $managers,
        ]);
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user has admin privileges
        if (!$user->hasRole(['super-admin', 'hr-admin'])) {
            return redirect()->route('departments.index')->with('error', 'You do not have permission to create departments.');
        }
        
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'required|string|max:10|unique:departments,code',
            'description' => 'nullable|string|max:1000',
            'manager_id' => 'nullable|exists:employees,id',
        ]);
        
        // Create the department
        $department = Department::create($validated);
        
        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified department.
     */
   /**
 * Display the specified department.
 */
public function show($id)
{
    // Get the department with related data
    $department = Department::with(['manager.user'])
                    ->findOrFail($id);
    
    // Get employees with pagination
    $employees = Employee::where('department_id', $id)
                    ->with('position')
                    ->paginate(10);
    
    // Transform department for frontend
    $departmentData = [
        'id' => $department->id,
        'name' => $department->name,
        'code' => $department->code,
        'description' => $department->description,
        'manager' => $department->manager ? [
            'id' => $department->manager->id,
            'name' => $department->manager->first_name . ' ' . $department->manager->last_name,
            'employee_id' => $department->manager->employee_id,
            'avatar' => $department->manager->profile_picture ?? '/placeholder.svg?height=40&width=40',
            'email' => $department->manager->user->email ?? null,
            'phone' => $department->manager->phone_number ?? null,
        ] : null,
        'created_at' => $department->created_at,
        'updated_at' => $department->updated_at,
    ];
    
    // Transform employees for frontend
    $employeesData = $employees->map(function ($employee) {
        return [
            'id' => $employee->id,
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'employee_id' => $employee->employee_id,
            'avatar' => $employee->profile_picture ?? '/placeholder.svg?height=40&width=40',
            'position' => $employee->position ? $employee->position->title : null,
            'email' => $employee->user->email ?? null,
            'phone' => $employee->phone_number ?? null,
            'hire_date' => $employee->hire_date,
        ];
    });
    
    return Inertia::render('Departments/Show', [
        'department' => $departmentData,
        'employees' => $employeesData,
        'pagination' => [
            'links' => $employees->linkCollection()->toArray(),
            'meta' => [
                'current_page' => $employees->currentPage(),
                'from' => $employees->firstItem(),
                'last_page' => $employees->lastPage(),
                'path' => $employees->path(),
                'per_page' => $employees->perPage(),
                'to' => $employees->lastItem(),
                'total' => $employees->total(),
            ],
        ],
        'isAdmin' => Auth::user()->hasRole(['super-admin', 'hr-admin']),
    ]);
}
    /**
     * Show the form for editing the specified department.
     */
    public function edit($id)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user has admin privileges
        if (!$user->hasRole(['super-admin', 'hr-admin'])) {
            return redirect()->route('departments.index')->with('error', 'You do not have permission to edit departments.');
        }
        
        // Get the department
        $department = Department::findOrFail($id);
        
        // Get managers (employees who are managers)
        $managers = Employee::whereHas('user', function ($query) {
            $query->role('manager');
        })->get()->map(function ($manager) {
            return [
                'id' => $manager->id,
                'name' => $manager->first_name . ' ' . $manager->last_name,
                'employee_id' => $manager->employee_id,
            ];
        });
        
        return Inertia::render('Departments/Edit', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'description' => $department->description,
                'manager_id' => $department->manager_id,
            ],
            'managers' => $managers,
        ]);
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, $id)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user has admin privileges
        if (!$user->hasRole(['super-admin', 'hr-admin'])) {
            return redirect()->route('departments.index')->with('error', 'You do not have permission to update departments.');
        }
        
        // Get the department
        $department = Department::findOrFail($id);
        
        // Validate the request
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments')->ignore($department->id),
            ],
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('departments')->ignore($department->id),
            ],
            'description' => 'nullable|string|max:1000',
            'manager_id' => 'nullable|exists:employees,id',
        ]);
        
        // Update the department
        $department->update($validated);
        
        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy($id)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user has admin privileges
        if (!$user->hasRole(['super-admin', 'hr-admin'])) {
            return redirect()->route('departments.index')->with('error', 'You do not have permission to delete departments.');
        }
        
        // Get the department
        $department = Department::findOrFail($id);
        
        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return redirect()->route('departments.index')->with('error', 'Cannot delete department with employees. Please reassign employees first.');
        }
        
        // Delete the department
        $department->delete();
        
        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }
}