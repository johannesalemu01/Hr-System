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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; 
use Illuminate\Pagination\LengthAwarePaginator; 

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     */
    public function index(Request $request)
    {
        
        $user = Auth::user();

        
        $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager', 'admin']);

        $departments = collect(); 
        $departmentsData = collect();
        $paginationData = null;
        $stats = null;
        $filters = ['search' => null];

        if ($isAdmin) {
            
            $search = $request->query('search');
            $filters['search'] = $search;

            
            $query = Department::with(['managerUser.employee']); 

            
            if ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            }

            
            $departmentsPaginator = $query->withCount('employees')
                                ->orderBy('name')
                                ->paginate(10);

            $departments = $departmentsPaginator->getCollection(); 

            
            $paginationData = [
                'links' => $departmentsPaginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $departmentsPaginator->currentPage(),
                    'from' => $departmentsPaginator->firstItem(),
                    'last_page' => $departmentsPaginator->lastPage(),
                    'path' => $departmentsPaginator->path(),
                    'per_page' => $departmentsPaginator->perPage(),
                    'to' => $departmentsPaginator->lastItem(),
                    'total' => $departmentsPaginator->total(),
                ],
            ];

            
            $totalDepartments = Department::count();
            $totalEmployees = Employee::count();
            $avgEmployeesPerDepartment = $totalDepartments > 0 ? round($totalEmployees / $totalDepartments, 1) : 0;
            $largestDepartment = Department::withCount('employees')
                                    ->orderBy('employees_count', 'desc')
                                    ->first();

            $stats = [
                'totalDepartments' => $totalDepartments,
                'totalEmployees' => $totalEmployees,
                'avgEmployeesPerDepartment' => $avgEmployeesPerDepartment,
                'largestDepartment' => $largestDepartment ? [
                    'name' => $largestDepartment->name,
                    'count' => $largestDepartment->employees_count,
                ] : null,
            ];

        } else {
            
            $employee = $user->employee; 
            if ($employee && $employee->department_id) {
                $department = Department::with(['managerUser.employee'])
                                ->withCount('employees')
                                ->find($employee->department_id);
                if ($department) {
                    $departments = collect([$department]); 
                }
            }
            
            
        }

        
        $departmentsData = $departments->map(function ($department) {
            $managerEmployee = $department->manager_employee; 
            $managerAvatar = $managerEmployee?->profile_picture
                            ? Storage::url($managerEmployee->profile_picture)
                            : null; 

            return [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'description' => $department->description,
                'manager' => $managerEmployee ? [
                    'id' => $managerEmployee->id,
                    'name' => $managerEmployee->first_name . ' ' . $managerEmployee->last_name,
                    'avatar' => $managerAvatar,
                ] : null,
                'employees_count' => $department->employees_count,
                'created_at' => $department->created_at,
            ];
        });

        return Inertia::render('Departments/index', [
            'departments' => $departmentsData,
            'filters' => $filters, 
            'stats' => $stats,     
            'isAdmin' => $isAdmin,
            'pagination' => $paginationData, 
        ]);
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {

        $user = Auth::user();
        if (!$user->hasRole(['super-admin', 'hr-admin', 'admin'])) {
            return redirect()->route('departments.index')->with('error', 'You do not have permission to create departments.');
        }

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
        
        $user = Auth::user();
        
        
        if (!$user->hasRole(['super-admin', 'hr-admin','admin'])) {
            return redirect()->route('departments.index')->with('error', 'You do not have permission to create departments.');
        }
        
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'required|string|max:10|unique:departments,code',
            'description' => 'nullable|string|max:1000',
            'manager_id' => 'nullable|exists:employees,id', 
        ]);

        
        if (!empty($validated['manager_id'])) {
            $employee = Employee::find($validated['manager_id']);
            if ($employee && $employee->user_id) {
                $validated['manager_id'] = $employee->user_id; 
            } else {
                Log::warning("Store Dept: Could not find user for employee ID {$validated['manager_id']}. Setting manager_id to null.");
                $validated['manager_id'] = null;
            }
        } else {
            $validated['manager_id'] = null;
        }
        

        
        Department::create($validated);
        
        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified department.
     */
    public function show($id)
    {
        
        $user = Auth::user();
        $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager', 'admin']);
        $department = Department::with(['managerUser.employee']) 
                        ->findOrFail($id);

        
        if (!$isAdmin) {
            $employee = $user->employee;
            if (!$employee || $employee->department_id != $id) {
                 
                 return redirect()->route('departments.index')->with('error', 'You do not have permission to view this department.');
            }
        }

        
        $employees = Employee::where('department_id', $id)
                        ->with(['position', 'user']) 
                        ->paginate(10);

        
        $managerEmployee = $department->manager_employee; 

        $managerAvatar = $managerEmployee?->profile_picture
                        ? Storage::url($managerEmployee->profile_picture)
                        : null;

        $departmentData = [
            'id' => $department->id,
            'name' => $department->name,
            'code' => $department->code,
            'description' => $department->description,
            'manager' => $managerEmployee ? [
                
                'id' => $managerEmployee->id, 
                'name' => $managerEmployee->first_name . ' ' . $managerEmployee->last_name,
                'employee_id' => $managerEmployee->employee_id, 
                'avatar' => $managerAvatar,
                'email' => $department->managerUser->email ?? null, 
                'phone' => $managerEmployee->phone_number ?? null,
            ] : null,
            'created_at' => $department->created_at,
            'updated_at' => $department->updated_at,
        ];

        
        $employeesData = $employees->map(function ($employee) {
            
             $avatarUrl = $employee->profile_picture
                ? Storage::url($employee->profile_picture)
                : null; 

            return [
                'id' => $employee->id, 
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'employee_id_text' => $employee->employee_id, 
                'profile_picture' => $avatarUrl, 
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
            'isAdmin' => $isAdmin, 
            'authUserId' => $user->id, 
            'authEmployeeId' => $user->employee?->id, 
        ]);
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit($id)
    {
        
        $user = Auth::user();
        
        
        if (!$user->hasRole(['super-admin', 'hr-admin','admin'])) {
            return redirect()->route('departments.index')->with('error', 'You do not have permission to edit departments.');
        }
        
        
        $department = Department::findOrFail($id); 
        
        
        $managers = Employee::whereHas('user', function ($query) {
            $query->role('manager');
        })->with('user') 
          ->get(['id', 'first_name', 'last_name', 'employee_id', 'user_id']) 
          ->map(function ($manager) {
            return [
                'id' => $manager->id, 
                'name' => $manager->first_name . ' ' . $manager->last_name,
                'user_id' => $manager->user_id, 
            ];
        });

        
        $selectedManagerEmployeeId = null;
        if ($department->manager_id) { 
            $currentManager = $managers->firstWhere('user_id', $department->manager_id);
            if ($currentManager) {
                $selectedManagerEmployeeId = $currentManager['id']; 
            } else {
                Log::warning("Edit Dept {$id}: Stored manager_id (user_id) {$department->manager_id} not found in current managers list.");
            }
        }
        

        return Inertia::render('Departments/Edit', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'description' => $department->description,
                
                'manager_id' => $selectedManagerEmployeeId,
            ],
            
            'managers' => $managers->values()->all(),
        ]);
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, $id)
    {
        
        $user = Auth::user();
        
        
        if (!$user->hasRole(['super-admin', 'hr-admin','admin'])) {
            return redirect()->route('departments.index')->with('error', 'You do not have permission to update departments.');
        }
        
        
        $department = Department::findOrFail($id);
        
        
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

        
        if (!empty($validated['manager_id'])) {
            $employee = Employee::find($validated['manager_id']);
            if ($employee && $employee->user_id) {
                $validated['manager_id'] = $employee->user_id; 
            } else {
                Log::warning("Update Dept {$id}: Could not find user for employee ID {$validated['manager_id']}. Setting manager_id to null.");
                $validated['manager_id'] = null;
            }
        } else {
            $validated['manager_id'] = null;
        }
        

        
        $department->update($validated);
        
        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy($id)
    {
        
        $user = Auth::user();
        
        
        if (!$user->hasRole(['super-admin','admin'])) {
            return redirect()->route('departments.index')->with('error', 'You do not have permission to delete departments.');
        }
        
        
        $department = Department::findOrFail($id);
        
        
        if ($department->employees()->count() > 0) {
            return redirect()->route('departments.index')->with('error', 'Cannot delete department with employees. Please reassign employees first.');
        }
        
        
        $department->delete();
        
        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }
}
