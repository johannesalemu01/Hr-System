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
use Illuminate\Support\Facades\Log; // Import Log facade

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
        $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager','admin']);
        
        // Get query parameters for filtering
        $search = $request->query('search');
        
        // Base query for departments
        $query = Department::with(['managerUser.employee']); // Use the new relationship path
        
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
            // Get manager details through the user->employee path
            $managerEmployee = $department->manager_employee; // Use the accessor

            $managerAvatar = $managerEmployee?->profile_picture
                            ? Storage::url($managerEmployee->profile_picture)
                            : null;

            return [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'description' => $department->description,
                'manager' => $managerEmployee ? [
                    // Use employee details
                    'id' => $managerEmployee->id, // Pass employee ID for consistency if needed elsewhere
                    'name' => $managerEmployee->first_name . ' ' . $managerEmployee->last_name,
                    'avatar' => $managerAvatar,
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
        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user has admin privileges
        if (!$user->hasRole(['super-admin', 'hr-admin','admin'])) {
            return redirect()->route('departments.index')->with('error', 'You do not have permission to create departments.');
        }
        
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'required|string|max:10|unique:departments,code',
            'description' => 'nullable|string|max:1000',
            'manager_id' => 'nullable|exists:employees,id', // Input is employee_id
        ]);

        // --- WORKAROUND: Convert employee_id to user_id ---
        if (!empty($validated['manager_id'])) {
            $employee = Employee::find($validated['manager_id']);
            if ($employee && $employee->user_id) {
                $validated['manager_id'] = $employee->user_id; // Replace with user_id
            } else {
                Log::warning("Store Dept: Could not find user for employee ID {$validated['manager_id']}. Setting manager_id to null.");
                $validated['manager_id'] = null;
            }
        } else {
            $validated['manager_id'] = null;
        }
        // --- END WORKAROUND ---

        // Create the department using user_id for manager_id
        Department::create($validated);
        
        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified department.
     */
    public function show($id)
    {
        // Get the department with related data
        $department = Department::with(['managerUser.employee']) // Use the new relationship path
                        ->findOrFail($id);

        // Get employees with pagination
        $employees = Employee::where('department_id', $id)
                        ->with(['position', 'user']) // Eager load position and user
                        ->paginate(10);

        // Get manager details through the user->employee path
        $managerEmployee = $department->manager_employee; // Use the accessor

        $managerAvatar = $managerEmployee?->profile_picture
                        ? Storage::url($managerEmployee->profile_picture)
                        : null;

        $departmentData = [
            'id' => $department->id,
            'name' => $department->name,
            'code' => $department->code,
            'description' => $department->description,
            'manager' => $managerEmployee ? [
                // Use employee details
                'id' => $managerEmployee->id, // Pass employee ID for link to employee profile
                'name' => $managerEmployee->first_name . ' ' . $managerEmployee->last_name,
                'employee_id' => $managerEmployee->employee_id, // Textual ID if needed
                'avatar' => $managerAvatar,
                'email' => $department->managerUser->email ?? null, // Get email from user
                'phone' => $managerEmployee->phone_number ?? null,
            ] : null,
            'created_at' => $department->created_at,
            'updated_at' => $department->updated_at,
        ];

        // Transform employees for frontend - PASS RELATIVE PATH
        $employeesData = $employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'employee_id' => $employee->employee_id,
                'profile_picture' => $employee->profile_picture, // Pass the relative path
                'position' => $employee->position ? $employee->position->title : null,
                'email' => $employee->user->email ?? null, // Ensure user is loaded if needed
                'phone' => $employee->phone_number ?? null,
                'hire_date' => $employee->hire_date,
            ];
        });

        return Inertia::render('Departments/Show', [
            'department' => $departmentData,
            'employees' => $employeesData, // Pass transformed data
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
            'isAdmin' => Auth::user()->hasRole(['super-admin', 'hr-admin','admin']),
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
        if (!$user->hasRole(['super-admin', 'hr-admin','admin'])) {
            return redirect()->route('departments.index')->with('error', 'You do not have permission to edit departments.');
        }
        
        // Get the department
        $department = Department::findOrFail($id); // manager_id here is user_id
        
        // Get managers (employees who are managers)
        $managers = Employee::whereHas('user', function ($query) {
            $query->role('manager');
        })->with('user') // Eager load user
          ->get(['id', 'first_name', 'last_name', 'employee_id', 'user_id']) // Include user_id
          ->map(function ($manager) {
            return [
                'id' => $manager->id, // employee_id for dropdown value
                'name' => $manager->first_name . ' ' . $manager->last_name,
                'user_id' => $manager->user_id, // user_id for lookup
            ];
        });

        // --- Find the employee_id to pre-select based on the stored user_id ---
        $selectedManagerEmployeeId = null;
        if ($department->manager_id) { // This is the user_id
            $currentManager = $managers->firstWhere('user_id', $department->manager_id);
            if ($currentManager) {
                $selectedManagerEmployeeId = $currentManager['id']; // Get the corresponding employee_id
            } else {
                Log::warning("Edit Dept {$id}: Stored manager_id (user_id) {$department->manager_id} not found in current managers list.");
            }
        }
        // --- End pre-selection logic ---

        return Inertia::render('Departments/Edit', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'description' => $department->description,
                // Pass the employee_id for pre-selection
                'manager_id' => $selectedManagerEmployeeId,
            ],
            // Pass managers list using employee_id as 'id'
            'managers' => $managers->values()->all(),
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
        if (!$user->hasRole(['super-admin', 'hr-admin','admin'])) {
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
            'manager_id' => 'nullable|exists:employees,id', // Input is employee_id
        ]);

        // --- WORKAROUND: Convert employee_id to user_id ---
        if (!empty($validated['manager_id'])) {
            $employee = Employee::find($validated['manager_id']);
            if ($employee && $employee->user_id) {
                $validated['manager_id'] = $employee->user_id; // Replace with user_id
            } else {
                Log::warning("Update Dept {$id}: Could not find user for employee ID {$validated['manager_id']}. Setting manager_id to null.");
                $validated['manager_id'] = null;
            }
        } else {
            $validated['manager_id'] = null;
        }
        // --- END WORKAROUND ---

        // Update the department using user_id for manager_id
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
        if (!$user->hasRole(['super-admin','admin'])) {
            return redirect()->route('departments.index')->with('error', 'You do not have permission to delete departments.');
        }
        
        // Get the department`
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
