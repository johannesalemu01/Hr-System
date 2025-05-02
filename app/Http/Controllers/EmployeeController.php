<?php                               

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\User; 
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\DB; 
use Illuminate\Validation\Rules; 
use Spatie\Permission\Models\Role; 

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees or show the user's own profile.
     */
    public function index(Request $request)
    {
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in.');
        }
        

        $user = Auth::user();

        
        
        $adminRoles = ['super-admin', 'admin', 'hr-admin', 'manager'];

        if (!$user->hasAnyRole($adminRoles)) {
            
            $employee = $user->employee()->with(['user', 'department', 'position'])->first(); 

            if ($employee) {
                
                return Inertia::render('Employees/Show', [
                    'employee' => $employee,
                ]);
            } else {
                
                Log::warning("EmployeeController@index: No employee record found for user ID {$user->id}. Redirecting to dashboard.");
                
                return redirect()->route('dashboard')->with('error', 'Your employee profile could not be found.');
            }
        }
        

        
        

        
        $search = $request->input('search'); 
        $departmentId = $request->input('department');
        $perPage = $request->input('per_page', 10);
        $employmentStatus = $request->input('employment_status');

        
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
            ->when($employmentStatus, function ($query, $employmentStatus) {
                return $query->where('employment_status', $employmentStatus);
            })
            ->orderBy('first_name');

        
        $employees = $employeesQuery->paginate($perPage)
            ->through(function ($employee) {
                
                
                $profilePictureUrl = $employee->profile_picture
                                    ? Storage::url($employee->profile_picture) 
                                    : null; 

                return [
                    'id' => $employee->id,
                    'employee_id' => $employee->employee_id,
                    'full_name' => $employee->first_name . ' ' . $employee->last_name,
                    'email' => $employee->user->email,
                    'department' => $employee->department->name,
                    'department_id' => $employee->department_id,
                    'position' => $employee->position->title,
                    'position_id' => $employee->position_id,
                    'profile_picture' => $profilePictureUrl, 
                    'hire_date' => $employee->hire_date,
                    'employment_status' => $employee->employment_status,
                ];
            });

        
        $departments = Department::select('id', 'name')->orderBy('name')->get();

        
        $allDepartments = [['id' => '', 'name' => 'All Departments']]; 
        $departmentOptions = array_merge($allDepartments, $departments->toArray());

        return Inertia::render('Employees/index', [
            'employees' => $employees,
            'departments' => $departmentOptions,
            'filters' => [
                'search' => $search,
                'department' => $departmentId,
                'employment_status' => $employmentStatus,
            ],
        ]);
    }


    public function uploadProfilePicture(Request $request, $employeeId)
    {
        $request->validate([
            'profile_picture' => 'required|image|max:2048',
        ]);

        $employee = Employee::findOrFail($employeeId);

        
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        
        if ($employee->profile_picture) {
            Storage::disk('public')->delete($employee->profile_picture);
        }

        $employee->update([
            'profile_picture' => $path,
        ]);

        return response()->json([
            'message' => 'Profile picture uploaded.',
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }    public function profile(Employee $employee)
    {
        return Inertia::render('Employees/Profile', [
            'employee' => $employee->load('user', 'department', 'position'),
        ]);
    }

    public function show(Employee $employee)
    {
        
        
        
        
        $user = Auth::user();
        if ($user->id !== $employee->user_id && !$user->can('view employees')) {
             abort(403, 'Unauthorized action.'); 
        }

        return Inertia::render('Employees/Show', [
            'employee' => $employee->load('user', 'department', 'position'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255', 
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email', 
            'password' => ['required', 'confirmed', Rules\Password::defaults()], 
            'employee_id' => 'required|string|max:255|unique:employees,employee_id', 
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed,other',
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'hire_date' => 'required|date',
            'employment_status' => 'required|in:full_time,part_time,contract,intern,probation,terminated,retired',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|max:2048',
            'role' => 'nullable|string|exists:roles,name', 
        ]);

        $profilePicturePath = null; 

        
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            if ($path) {
                $validated['profile_picture'] = $path; 
                $profilePicturePath = $path; 
            } else {
                 Log::error('Failed to store profile picture during employee creation.');
                 return back()->withInput()->with('error', 'Failed to store profile picture.');
            }
        }

        
        DB::beginTransaction();
        try {
            
            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'], 
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'profile_picture' => $profilePicturePath, 
            ]);

            
            if (!empty($validated['role'])) {
                $user->assignRole($validated['role']); 
            } else {
                
                
            }

            
            $validated['user_id'] = $user->id;

            
            
            $employeeData = collect($validated)->except('role')->toArray();
            Employee::create($employeeData);

            
            DB::commit();

            return redirect()->route('employees.index')->with('success', 'Employee added successfully.');

        } catch (\Exception $e) {
            
            DB::rollBack();

            
            Log::error('Error creating employee: ' . $e->getMessage());

            
            if ($profilePicturePath) {
                Storage::disk('public')->delete($profilePicturePath);
            }

            
            return back()->withInput()->with('error', 'Failed to add employee. Please try again.');
        }
    }

    public function create()
    {
        
        

        
        $departments = Department::orderBy('name')->get(['id', 'name']); 
        $positions = Position::orderBy('title')->get(['id', 'title']); 
        $roles = Role::orderBy('name')->get(['id', 'name']); 

        return Inertia::render('Employees/Create', [
            'departments' => $departments,
            'positions' => $positions,
            'roles' => $roles, 
        ]);
    }

    public function createData()
    {
        $departments = Department::select('id', 'name')->orderBy('name')->get();
        $positions = Position::select('id', 'title')->orderBy('title')->get();

        return response()->json([
            'departments' => $departments,
            'positions' => $positions,
        ]);
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee)
    {
        
        $this->authorize('edit employees');

        
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $positions = Position::orderBy('title')->get(['id', 'title']);
        $roles = Role::orderBy('name')->get(['id', 'name']); 

        
        $employee->load('user.roles', 'department', 'position');

        
        $currentRoleName = $employee->user ? $employee->user->roles->first()?->name : null;

        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
            'departments' => $departments,
            'positions' => $positions,
            'roles' => $roles, 
            'currentRoleName' => $currentRoleName, 
        ]);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        
        $this->authorize('edit employees');

        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            
            
            'employee_id' => 'required|string|max:255|unique:employees,employee_id,' . $employee->id, 
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed,other',
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'hire_date' => 'required|date',
            'employment_status' => 'required|in:full_time,part_time,contract,intern,probation,terminated,retired',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'role' => 'nullable|string|exists:roles,name', 
            'profile_picture' => 'nullable|image|max:2048', 
        ]);

        $newProfilePicturePath = null; 
        
        $oldProfilePicturePath = $employee->profile_picture;

        
        if ($request->hasFile('profile_picture')) {
            
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            if ($path) {
                $validated['profile_picture'] = $path; 
                $newProfilePicturePath = $path; 

                
                if ($oldProfilePicturePath && $oldProfilePicturePath !== $newProfilePicturePath) {
                    Storage::disk('public')->delete($oldProfilePicturePath);
                }
            } else {
                 Log::error('Failed to store new profile picture during employee update.');
                 return back()->withInput()->with('error', 'Failed to store new profile picture.');
            }

        } else {
            unset($validated['profile_picture']); 
        }

        
        
        $employeeData = collect($validated)->except('role')->toArray();
        $employee->update($employeeData);

        
        if ($employee->user) {
             $userDataToUpdate = [];
             if ($request->filled('first_name') || $request->filled('last_name')) {
                 $userDataToUpdate['name'] = $validated['first_name'] . ' ' . $validated['last_name'];
             }
             if ($newProfilePicturePath !== null) {
                 $userDataToUpdate['profile_picture'] = $newProfilePicturePath;
             }
             if (!empty($userDataToUpdate)) {
                 $employee->user->update($userDataToUpdate);
             }

             
             if ($request->filled('role')) {
                 $employee->user->syncRoles([$validated['role']]); 
             } else {
                 
                 
             }
        }

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee)
    {
        
        $this->authorize('delete employees');

        try {
            
            
            
            
            

            
            if ($employee->profile_picture) {
                Storage::disk('public')->delete($employee->profile_picture);
            }

            
            $employee->delete();

            return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Error deleting employee: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete employee. Please try again.');
        }
    }

    
}
