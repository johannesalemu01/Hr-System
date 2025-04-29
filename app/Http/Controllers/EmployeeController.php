<?php                               

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\User; // Import the User model
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash; // Import Hash facade
use Illuminate\Support\Facades\DB; // Import DB facade for transaction
use Illuminate\Validation\Rules; // Import Rules for password validation
use Spatie\Permission\Models\Role; // Import Role model

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
        $search = $request->input('search'); // Ensure this is being captured
        $departmentId = $request->input('department');
        $perPage = $request->input('per_page', 10);
        $employmentStatus = $request->input('employment_status');

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
            ->when($employmentStatus, function ($query, $employmentStatus) {
                return $query->where('employment_status', $employmentStatus);
            })
            ->orderBy('first_name');

        // Paginate the results
        $employees = $employeesQuery->paginate($perPage)
            ->through(function ($employee) {
                // Generate full URL for profile picture if it exists
                $profilePictureUrl = $employee->profile_picture
                                    ? Storage::url($employee->profile_picture)
                                    : null; // Or a default placeholder URL

                return [
                    'id' => $employee->id,
                    'employee_id' => $employee->employee_id,
                    'full_name' => $employee->first_name . ' ' . $employee->last_name,
                    'email' => $employee->user->email,
                    'department' => $employee->department->name,
                    'department_id' => $employee->department_id,
                    'position' => $employee->position->title,
                    'position_id' => $employee->position_id,
                    'profile_picture' => $profilePictureUrl, // Use the generated URL
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

    // Store in /storage/app/public/profile_pictures
    $path = $request->file('profile_picture')->store('profile_pictures', 'public');

    // Optional: delete old picture if exists
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
        return Inertia::render('Employees/Show', [
            'employee' => $employee->load('user', 'department', 'position'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255', // Often required
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email', // Add email validation for user
            'password' => ['required', 'confirmed', Rules\Password::defaults()], // Add password validation for user
            'employee_id' => 'required|string|max:255|unique:employees,employee_id', // Ensure employee_id is unique
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
            'role' => 'nullable|string|exists:roles,name', // Add role validation
        ]);

        $profilePicturePath = null; // Variable to hold the path

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            if ($path) {
                $validated['profile_picture'] = $path; // Store PATH in validated data
                $profilePicturePath = $path; // Store the path for user update and potential deletion
            } else {
                 Log::error('Failed to store profile picture during employee creation.');
                 return back()->withInput()->with('error', 'Failed to store profile picture.');
            }
        }

        // Use a database transaction to ensure both user and employee are created or neither
        DB::beginTransaction();
        try {
            // 1. Create the User
            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'], // Combine names for user name
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'profile_picture' => $profilePicturePath, // Store path in user table
            ]);

            // Assign the selected role if provided
            if (!empty($validated['role'])) {
                $user->assignRole($validated['role']); // Assign the role
            } else {
                // Optional: Assign a default role if none is selected
                // $user->assignRole('employee'); 
            }

            // 2. Add user_id to the validated data for the employee
            $validated['user_id'] = $user->id;

            // 3. Create the Employee, linking it to the user
            // Exclude 'role' from employee creation data
            $employeeData = collect($validated)->except('role')->toArray();
            Employee::create($employeeData);

            // If everything went well, commit the transaction
            DB::commit();

            return redirect()->route('employees.index')->with('success', 'Employee added successfully.');

        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();

            // Log the error for debugging
            Log::error('Error creating employee: ' . $e->getMessage());

            // Optionally delete the uploaded file if creation failed
            if ($profilePicturePath) {
                Storage::disk('public')->delete($profilePicturePath);
            }

            // Redirect back with an error message
            return back()->withInput()->with('error', 'Failed to add employee. Please try again.');
        }
    }

    public function create()
    {
        // Temporarily comment out the authorization check for debugging
        // $this->authorize('create employees');

        // Fetch necessary data for the form (e.g., departments, positions)
        $departments = Department::orderBy('name')->get(['id', 'name']); 
        $positions = Position::orderBy('title')->get(['id', 'title']); 
        $roles = Role::orderBy('name')->get(['id', 'name']); // Fetch roles

        return Inertia::render('Employees/Create', [
            'departments' => $departments,
            'positions' => $positions,
            'roles' => $roles, // Pass roles to the view
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
        // Authorize the action
        $this->authorize('edit employees');

        // Fetch necessary data for the form
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $positions = Position::orderBy('title')->get(['id', 'title']);
        $roles = Role::orderBy('name')->get(['id', 'name']); // Fetch all roles

        // Load related data including the user and their roles
        $employee->load('user.roles', 'department', 'position');

        // Get the first role name of the user (assuming one role per user for simplicity in form)
        $currentRoleName = $employee->user ? $employee->user->roles->first()?->name : null;

        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
            'departments' => $departments,
            'positions' => $positions,
            'roles' => $roles, // Pass roles to the view
            'currentRoleName' => $currentRoleName, // Pass current role name
        ]);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        // Authorize the action
        $this->authorize('edit employees');

        // Validation rules (similar to store, but adjust unique constraints)
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            // Email/Password updates might be handled separately in user profile section
            // 'email' => 'required|string|email|max:255|unique:users,email,' . $employee->user_id,
            'employee_id' => 'required|string|max:255|unique:employees,employee_id,' . $employee->id, // Ignore current employee
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
            'role' => 'nullable|string|exists:roles,name', // Validate the role name exists
            'profile_picture' => 'nullable|image|max:2048', // Handle file update separately if needed
        ]);

        $newProfilePicturePath = null; // Variable to hold the new path
        // Get old path directly from model (assuming it stores the path)
        $oldProfilePicturePath = $employee->profile_picture;

        // Handle profile picture update
        if ($request->hasFile('profile_picture')) {
            // Store new picture first
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            if ($path) {
                $validated['profile_picture'] = $path; // Put new PATH in validated data
                $newProfilePicturePath = $path; // Store the new path for user update

                // Delete old picture using its path *after* storing the new one
                if ($oldProfilePicturePath && $oldProfilePicturePath !== $newProfilePicturePath) {
                    Storage::disk('public')->delete($oldProfilePicturePath);
                }
            } else {
                 Log::error('Failed to store new profile picture during employee update.');
                 return back()->withInput()->with('error', 'Failed to store new profile picture.');
            }

        } else {
            unset($validated['profile_picture']); // Don't overwrite if no new file
        }

        // Update the employee record (contains PATH if changed)
        // Exclude 'role' from employee update data as it belongs to the user
        $employeeData = collect($validated)->except('role')->toArray();
        $employee->update($employeeData);

        // Update user record (name, profile picture, and role)
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

             // Sync the user's role if a role was provided in the request
             if ($request->filled('role')) {
                 $employee->user->syncRoles([$validated['role']]); // Sync roles (expects an array or collection)
             } else {
                 // Optional: Remove all roles if no role is selected? Or leave as is?
                 // $employee->user->syncRoles([]); // Uncomment to remove all roles if none selected
             }
        }

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee)
    {
        // Authorize the action
        $this->authorize('delete employees');

        try {
            // Optional: Delete associated user account if desired
            // Be careful with this - ensure it's the intended behavior
            // if ($employee->user) {
            //     $employee->user->delete();
            // }

            // Optional: Delete profile picture from storage
            if ($employee->profile_picture) {
                Storage::disk('public')->delete($employee->profile_picture);
            }

            // Delete the employee record
            $employee->delete();

            return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Error deleting employee: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete employee. Please try again.');
        }
    }

    // Other controller methods (show, create, store, edit, update, destroy)...
}
