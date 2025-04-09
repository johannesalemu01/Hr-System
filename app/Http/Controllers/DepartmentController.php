<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
        // Get managers for dropdown
        $managers = Employee::whereHas('user', function ($query) {
            $query->role('manager');
        })->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
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
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'code' => 'required|string|max:10|unique:departments',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
        ]);
        
        Department::create($validated);
        
        return redirect()->route('departments.index')
                        ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified department.
     */
    public function show($id)
    {
        $department = Department::with(['manager.user', 'employees'])
                                ->findOrFail($id);
        
        // Get employees in this department
        $employees = $department->employees()
                                ->with('position')
                                ->paginate(10);
        
        return Inertia::render('Departments/Show', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'description' => $department->description,
                'manager' => $department->manager ? [
                    'id' => $department->manager->id,
                    'name' => $department->manager->first_name . ' ' . $department->manager->last_name,
                    'avatar' => $department->manager->profile_picture ?? '/placeholder.svg?height=40&width=40',
                ] : null,
                'created_at' => $department->created_at,
            ],
            'employees' => $employees->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'position' => $employee->position ? $employee->position->title : 'N/A',
                    'avatar' => $employee->profile_picture ?? '/placeholder.svg?height=40&width=40',
                    'hire_date' => $employee->hire_date,
                ];
            }),
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
        ]);
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit($id)
    {
        $department = Department::findOrFail($id);
        
        // Get managers for dropdown
        $managers = Employee::whereHas('user', function ($query) {
            $query->role('manager');
        })->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
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
        $department = Department::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
            'code' => 'required|string|max:10|unique:departments,code,' . $id,
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
        ]);
        
        $department->update($validated);
        
        return redirect()->route('departments.index')
                        ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        
        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return redirect()->back()
                            ->with('error', 'Cannot delete department with employees. Please reassign employees first.');
        }
        
        $department->delete();
        
        return redirect()->route('departments.index')
                        ->with('success', 'Department deleted successfully.');
    }
}