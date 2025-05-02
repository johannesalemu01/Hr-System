<?php

namespace App\Http\Controllers;

use App\Models\Kpi;
use App\Models\Employee;
use App\Models\EmployeeKpi;
use App\Models\KpiRecord;
use App\Models\Department;
use App\Models\Position;
use App\Models\Badge; // Add Badge model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse; // Import RedirectResponse
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder; // Import Builder

class KpiController extends Controller
{
    // Define admin roles for easier checking
    private $adminRoles = ['super-admin', 'admin', 'hr-admin', 'manager'];

    /**
     * Display a listing of KPIs. (Admin/Manager only)
     */
    public function index(Request $request)
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view KPIs.');
        }
        // --- End Authentication Check ---

        $user = Auth::user();
        // --- Authorization ---
        if (!$user->hasAnyRole($this->adminRoles)) {
             return redirect()->route('kpis.employee-kpis')->with('message', 'Viewing your assigned KPIs.'); // Redirect employee to their list
        }
        // --- End Authorization ---

        // Check permissions (using Spatie policies if set up)
        // $this->authorize('viewAny', Kpi::class); // Uncomment if using policies

        // Get query parameters for filtering
        $departmentId = $request->query('department_id');
        $search = $request->query('search');
        $status = $request->query('status');
        
        // Base query for KPIs
        $query = Kpi::with(['department', 'position']);
        
        // Apply filters
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('measurement_unit', 'like', "%{$search}%");
            });
        }
        
        if ($status) {
            $query->where('is_active', $status === 'active');
        }
        
        // Get paginated results
        $kpis = $query->orderBy('name')
            ->paginate(10)
            ->through(function ($kpi) {
                return [
                    'id' => $kpi->id,
                    'name' => $kpi->name,
                    'description' => $kpi->description,
                    'measurement_unit' => $kpi->measurement_unit,
                    'frequency' => $kpi->frequency,
                    'department' => $kpi->department ? $kpi->department->name : 'N/A',
                    'department_id' => $kpi->department_id,
                    'position' => $kpi->position ? $kpi->position->title : 'N/A',
                    'position_id' => $kpi->position_id,
                    'is_active' => $kpi->is_active,
                    'points_value' => $kpi->points_value,
                    'created_at' => $kpi->created_at,
                    'updated_at' => $kpi->updated_at,
                ];
            });
        
        // Get all departments for filtering
        $departments = Department::orderBy('name')->get();
        
        // Get KPI statistics
        $stats = [
            'total' => Kpi::count(),
            'active' => Kpi::where('is_active', true)->count(),
            'inactive' => Kpi::where('is_active', false)->count(),
            'departments' => Department::withCount('kpis')->get()->map(function ($dept) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->name,
                    'count' => $dept->kpis_count,
                ];
            }),
        ];
        
        return Inertia::render('Kpis/index', [
            'kpis' => $kpis,
            'departments' => $departments,
            'stats' => $stats,
            'filters' => [
                'department_id' => $departmentId,
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Show the form for creating a new KPI. (Admin/Manager only)
     */
    public function create()
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to create KPIs.');
        }
        // --- End Authentication Check ---

        $user = Auth::user();
        // --- Authorization ---
        if (!$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        // --- End Authorization ---
        // $this->authorize('create', Kpi::class); // Uncomment if using policies

        // Get all departments and positions for the form
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();
        
        return Inertia::render('Kpis/Create', [
            'departments' => $departments,
            'positions' => $positions,
        ]);
    }

    /**
     * Store a newly created KPI in storage. (Admin/Manager only)
     */
    public function store(Request $request)
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to store KPIs.');
        }
        // --- End Authentication Check ---

        $user = Auth::user();
        // --- Authorization ---
        if (!$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        // --- End Authorization ---
        // $this->authorize('create', Kpi::class); // Uncomment if using policies

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'measurement_unit' => 'required|string|max:50',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,annually',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'is_active' => 'boolean',
            'points_value' => 'required|integer|min:1|max:100',
        ]);
        
        // Create the KPI
        Kpi::create($validated);
        
        return redirect()->route('kpis.index')->with('success', 'KPI created successfully.');
    }

    /**
     * Display the specified KPI. (Filter for employee role)
     */
    public function show($id)
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view this KPI.');
        }
        // --- End Authentication Check ---

        $user = Auth::user();
        $kpi = Kpi::with(['department', 'position'])->findOrFail($id);
        $isEmployeeOnly = !$user->hasAnyRole($this->adminRoles);
        $employeeId = $user->employee->id ?? null;

        // --- Authorization/Filtering ---
        if ($isEmployeeOnly) {
            if (!$employeeId) {
                abort(403, 'Employee record not found for user.');
            }
            // Check if this employee is assigned this KPI
            $isAssigned = EmployeeKpi::where('kpi_id', $id)
                                     ->where('employee_id', $employeeId)
                                     ->exists();
            if (!$isAssigned) {
                 abort(403, 'You are not assigned this KPI.');
            }
            // Filter employeeKpis and kpiRecords to only show the current user's data
             $employeeKpis = EmployeeKpi::where('kpi_id', $id)
                ->where('employee_id', $employeeId) // Filter for self
                ->with(['employee', 'employee.department', 'employee.position']) // Keep relations needed for formatting
                ->get();

             $kpiRecords = KpiRecord::whereHas('employeeKpi', function($query) use ($id, $employeeId) {
                 $query->where('kpi_id', $id)->where('employee_id', $employeeId); // Filter for self
             })
             ->with(['employeeKpi.employee']) // Keep relations needed for formatting
             ->orderBy('record_date', 'desc')
             ->take(10) // Or adjust limit as needed
             ->get();

        } else {
            // Admin view: Get all assigned employees and records for this KPI
            // $this->authorize('view', $kpi); // Uncomment if using policies

            $employeeKpis = EmployeeKpi::where('kpi_id', $id)
                ->with(['employee', 'employee.department', 'employee.position'])
                ->get();

            $kpiRecords = KpiRecord::whereHas('employeeKpi', function($query) use ($id) {
                $query->where('kpi_id', $id);
            })
            ->with(['employeeKpi.employee'])
            ->orderBy('record_date', 'desc')
            ->take(10) // Or adjust limit as needed
            ->get();
        }
        // --- End Authorization/Filtering ---


        // Format KPI data
        $kpiData = [
            'id' => $kpi->id,
            'name' => $kpi->name,
            'description' => $kpi->description,
            'measurement_unit' => $kpi->measurement_unit,
            'frequency' => $kpi->frequency,
            'department' => $kpi->department ? $kpi->department->name : 'N/A',
            'department_id' => $kpi->department_id,
            'position' => $kpi->position ? $kpi->position->title : 'N/A',
            'position_id' => $kpi->position_id,
            'is_active' => $kpi->is_active,
            'points_value' => $kpi->points_value,
            'created_at' => $kpi->created_at,
            'updated_at' => $kpi->updated_at,
        ];
        
        // Format employee KPIs
        $formattedEmployeeKpis = $employeeKpis->map(function ($empKpi) {
            return [
                'id' => $empKpi->id,
                'employee' => [
                    'id' => $empKpi->employee->id,
                    'name' => $empKpi->employee->full_name,
                    'employee_id' => $empKpi->employee->employee_id,
                    'department' => $empKpi->employee->department ? $empKpi->employee->department->name : 'N/A',
                    'position' => $empKpi->employee->position ? $empKpi->employee->position->title : 'N/A',
                ],
                'target_value' => $empKpi->target_value,
                'minimum_value' => $empKpi->minimum_value,
                'maximum_value' => $empKpi->maximum_value,
                'weight' => $empKpi->weight,
                'start_date' => $empKpi->start_date,
                'end_date' => $empKpi->end_date,
                'status' => $empKpi->status,
            ];
        });
        
        // Format KPI records
        $formattedKpiRecords = $kpiRecords->map(function ($record) {
            return [
                'id' => $record->id,
                'employee' => $record->employeeKpi->employee->full_name,
                'actual_value' => $record->actual_value,
                'target_value' => $record->employeeKpi->target_value,
                'achievement_percentage' => $record->achievement_percentage,
                'record_date' => $record->record_date,
                'points_earned' => $record->points_earned,
            ];
        });
        
        // Calculate average achievement based on filtered records
        $avgAchievement = $kpiRecords->avg('achievement_percentage');

        // Get performance trend data (pass employeeId if filtering trend)
        $trendData = $this->getPerformanceTrendData($id, $isEmployeeOnly ? $employeeId : null);

        return Inertia::render('Kpis/Show', [
            'kpi' => $kpiData,
            'employeeKpis' => $formattedEmployeeKpis,
            'kpiRecords' => $formattedKpiRecords,
            'stats' => [
                'avgAchievement' => round($avgAchievement, 2),
                'employeeCount' => $employeeKpis->count(), // Count based on filtered list
                'recordsCount' => $kpiRecords->count(), // Count based on filtered list
            ],
            'trendData' => $trendData,
            'isEmployeeView' => $isEmployeeOnly, // Pass flag to frontend
        ]);
    }

    /**
     * Show the form for editing the specified KPI. (Admin/Manager only)
     */
    public function edit($id)
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to edit KPIs.');
        }
        // --- End Authentication Check ---

        $user = Auth::user();
         // --- Authorization ---
        if (!$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        // --- End Authorization ---
        // $kpi = Kpi::findOrFail($id);
        // $this->authorize('update', $kpi); // Uncomment if using policies

        // Get the KPI
        $kpi = Kpi::with(['department', 'position'])->findOrFail($id);
        
        // Get employees assigned to this KPI
        $employeeKpis = EmployeeKpi::where('kpi_id', $id)
            ->with(['employee', 'employee.department', 'employee.position'])
            ->get()
            ->map(function ($empKpi) {
                return [
                    'id' => $empKpi->id,
                    'employee' => [
                        'id' => $empKpi->employee->id,
                        'name' => $empKpi->employee->full_name,
                        'department' => $empKpi->employee->department ? $empKpi->employee->department->name : 'N/A',
                    ],
                    'target_value' => $empKpi->target_value,
                    'start_date' => $empKpi->start_date,
                    'end_date' => $empKpi->end_date,
                    'status' => $empKpi->status,
                ];
            });

        // Get KPI records for this KPI
        $kpiRecords = KpiRecord::whereHas('employeeKpi', function ($query) use ($id) {
            $query->where('kpi_id', $id);
        })
        ->with(['employeeKpi.employee'])
        ->orderBy('record_date', 'desc')
        ->take(10)
        ->get()
        ->map(function ($record) {
            return [
                'id' => $record->id,
                'employee' => $record->employeeKpi->employee->full_name,
                'actual_value' => $record->actual_value,
                'target_value' => $record->employeeKpi->target_value,
                'achievement_percentage' => $record->achievement_percentage,
                'record_date' => $record->record_date,
                'points_earned' => $record->points_earned,
            ];
        });

        // Calculate stats
        $stats = [
            'avgAchievement' => round($kpiRecords->avg('achievement_percentage'), 2),
            'employeeCount' => $employeeKpis->count(),
        ];

        // Get performance trend data
        $trendData = $this->getPerformanceTrendData($id);

        // Get all departments and positions for the form
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();

        return Inertia::render('Kpis/Edit', [
            'kpi' => $kpi,
            'employeeKpis' => $employeeKpis,
            'kpiRecords' => $kpiRecords,
            'stats' => $stats,
            'trendData' => $trendData,
            'departments' => $departments,
            'positions' => $positions,
        ]);
    }

    /**
     * Update the specified KPI in storage. (Admin/Manager only)
     */
    public function update(Request $request, $id)
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to update KPIs.');
        }
        // --- End Authentication Check ---

        $user = Auth::user();
         // --- Authorization ---
        if (!$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        // --- End Authorization ---
        // $kpi = Kpi::findOrFail($id);
        // $this->authorize('update', $kpi); // Uncomment if using policies

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'measurement_unit' => 'required|string|max:50',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,annually',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'is_active' => 'boolean',
            'points_value' => 'required|integer|min:1|max:100',
        ]);
        
        // Update the KPI
        $kpi = Kpi::findOrFail($id);
        $kpi->update($validated);
        
        return redirect()->route('kpis.index')->with('success', 'KPI updated successfully.');
    }

    /**
     * Remove the specified KPI from storage. (Admin/Manager only)
     */
    public function destroy($id): RedirectResponse
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to delete KPIs.');
        }
        // --- End Authentication Check ---

        $user = Auth::user();
         // --- Authorization ---
        if (!$user->hasAnyRole($this->adminRoles)) {
             return redirect()->back()->with('error', 'You do not have permission to delete KPIs.');
        }
        // --- End Authorization ---
        // $kpi = Kpi::findOrFail($id);
        // $this->authorize('delete', $kpi); // Uncomment if using policies

        Log::info("Attempting to delete KPI with ID: {$id}");

        try {
            // Find the KPI first
            $kpi = Kpi::find($id);

            if (!$kpi) {
                Log::warning("KPI not found for ID: {$id}");
                // Revert to redirect with flash message
                return redirect()->back()->with('error', 'KPI not found.');
            }
            Log::info("KPI found: " . $kpi->name);


            // Check if KPI is in use
            $inUse = EmployeeKpi::where('kpi_id', $id)->exists();
            Log::info("Checking if KPI ID {$id} is in use: " . ($inUse ? 'Yes' : 'No'));

            if ($inUse) {
                Log::warning("Attempted to delete KPI ID {$id} which is in use.");
                // Revert to redirect with flash message
                return redirect()->back()->with('error', 'Cannot delete KPI because it is assigned to employees.');
            }

            // Delete the KPI
            Log::info("Proceeding to delete KPI ID: {$id}");
            $deleted = $kpi->delete();

            if ($deleted) {
                Log::info("Successfully deleted KPI ID: {$id}");
                // On successful deletion, redirect to index
                return redirect()->route('kpis.index')->with('success', 'KPI deleted successfully.');
            } else {
                Log::error("Failed to delete KPI ID: {$id} from database.");
                 // Revert to redirect with flash message
                return redirect()->back()->with('error', 'Failed to delete KPI from database.');
            }

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::error("Authorization failed for deleting KPI ID: {$id}. Error: " . $e->getMessage());
             // Revert to redirect with flash message
            return redirect()->back()->with('error', 'You do not have permission to delete KPIs.');
        } catch (\Exception $e) {
            Log::error("An error occurred while deleting KPI ID: {$id}. Error: " . $e->getMessage());
             // Revert to redirect with flash message
            return redirect()->back()->with('error', 'An unexpected error occurred while deleting the KPI.');
        }
    }

    /**
     * Display a listing of employee KPIs. Filter for employee role.
     */
    public function employeeKpis(Request $request)
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view employee KPIs.');
        }
        // --- End Authentication Check ---

        $user = Auth::user();
        $isEmployeeOnly = !$user->hasAnyRole($this->adminRoles);
        $employeeId = $user->employee->id ?? null;

        // Base query for employee KPIs
        $query = EmployeeKpi::with(['employee', 'employee.department', 'employee.position', 'kpi']);

        // --- Role-Based Filtering ---
        if ($isEmployeeOnly) {
            if (!$employeeId) {
                 $query->whereRaw('1 = 0'); // No results if no employee linked
                 Log::warning("EmployeeKPIs: No employee record found for user ID {$user->id}");
            } else {
                $query->where('employee_id', $employeeId);
            }
            // Ignore admin filters
            $departmentId = null;
            $search = null;
            $status = null;
        } else {
            // Admin view - apply filters
            // $this->authorize('viewAny', EmployeeKpi::class); // Uncomment if using policies

            $departmentId = $request->query('department_id');
            $search = $request->query('search');
            $status = $request->query('status');

            if ($departmentId) {
                $query->whereHas('employee', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }
            if ($search) {
                 $query->where(function(Builder $q) use ($search) { // Use Builder type hint
                     $q->whereHas('employee', function(Builder $subQ) use ($search) { // Use Builder type hint
                         $subQ->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                              ->orWhere('employee_id', 'like', "%{$search}%");
                     })->orWhereHas('kpi', function(Builder $subQ) use ($search) { // Use Builder type hint
                         $subQ->where('name', 'like', "%{$search}%");
                     });
                 });
            }
            if ($status) {
                $query->where('status', $status);
            }
        }
        // --- End Role-Based Filtering ---

        // Get paginated results
        $employeeKpis = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($empKpi) {
                // Calculate current value and achievement percentage here if needed for the list view
                $currentValue = $this->getCurrentValue($empKpi->id);
                $achievementPercentage = $this->getAchievementPercentage($empKpi->id, $currentValue, $empKpi->target_value); // Pass values to helper

                return [
                    'id' => $empKpi->id,
                    'employee' => [
                        'id' => $empKpi->employee->id,
                        'name' => $empKpi->employee->full_name,
                        'employee_id' => $empKpi->employee->employee_id,
                        'department' => $empKpi->employee->department?->name, // Use null safe operator
                        'position' => $empKpi->employee->position?->title, // Use null safe operator
                    ],
                    'kpi' => [
                        'id' => $empKpi->kpi->id,
                        'name' => $empKpi->kpi->name,
                        'measurement_unit' => $empKpi->kpi->measurement_unit,
                    ],
                    'target_value' => $empKpi->target_value,
                    'current_value' => $currentValue, // Use calculated value
                    'achievement_percentage' => $achievementPercentage, // Use calculated value
                    'start_date' => $empKpi->start_date,
                    'end_date' => $empKpi->end_date,
                    'status' => $empKpi->status,
                ];
            });

        // Get all departments for filtering (only for admins)
        $departments = $isEmployeeOnly ? [] : Department::orderBy('name')->get();

        return Inertia::render('Kpis/EmployeeKpis', [
            'employeeKpis' => $employeeKpis,
            'departments' => $departments,
            'statuses' => ['active', 'pending', 'completed'],
            'filters' => [
                'department_id' => $isEmployeeOnly ? null : $departmentId,
                'search' => $isEmployeeOnly ? null : $search,
                'status' => $isEmployeeOnly ? null : $status,
            ],
            'isEmployeeView' => $isEmployeeOnly,
        ]);
    }

    /**
     * Show the form for assigning a KPI to an employee. (Admin/Manager only)
     */
    public function assignKpi($kpi_id)
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to assign KPIs.');
        }
        // --- End Authentication Check ---

        $user = Auth::user();
         // --- Authorization ---
        if (!$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        // --- End Authorization ---
        // $this->authorize('create', EmployeeKpi::class); // Uncomment if using policies

        // Get the specified KPI
        $kpi = Kpi::findOrFail($kpi_id);

        // Get all employees
        $employees = Employee::whereNull('termination_date')
            ->with(['department', 'position'])
            ->orderBy('first_name')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->full_name,
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department ? $employee->department->name : 'N/A',
                    'position' => $employee->position ? $employee->position->title : 'N/A',
                ];
            });

        return Inertia::render('Kpis/AssignKpi', [
            'employees' => $employees,
            'kpi' => [
                'id' => $kpi->id,
                'name' => $kpi->name,
                'measurement_unit' => $kpi->measurement_unit,
                'department' => $kpi->department ? $kpi->department->name : 'N/A',
            ],
        ]);
    }

    /**
     * Store a newly created employee KPI in storage. (Admin/Manager only)
     */
    public function storeEmployeeKpi(Request $request)
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to assign KPIs.');
        }
        // --- End Authentication Check ---

        $user = Auth::user();
         // --- Authorization ---
        if (!$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        // --- End Authorization ---
        // $this->authorize('create', EmployeeKpi::class); // Uncomment if using policies

        // Validate the request
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'kpi_id' => 'required|exists:kpis,id',
            'target_value' => 'required|numeric',
            'minimum_value' => 'required|numeric|lte:target_value',
            'maximum_value' => 'required|numeric|gte:target_value',
            'weight' => 'required|numeric|min:0.1|max:5',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);
        
        // Add additional fields
        $validated['status'] = 'active';
        $validated['assigned_by'] = Auth::id();
        
        // Create the employee KPI
        EmployeeKpi::create($validated);
        
        return redirect()->route('kpis.employee-kpis')->with('success', 'KPI assigned to employee successfully.');
    }

    /**
     * Show the form for recording a KPI value. (Allow self-recording OR admin recording)
     */
    public function recordKpi($id)
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to record KPI values.');
        }
        // --- End Authentication Check ---

        $employeeKpi = EmployeeKpi::with(['employee', 'kpi'])->findOrFail($id);
        $user = Auth::user();

        // --- Authorization ---
        if ($employeeKpi->employee_id !== ($user->employee->id ?? null) && !$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        // $this->authorize('create', KpiRecord::class); // Check general permission
        // $this->authorize('update', $employeeKpi); // Check if user can update this specific EmployeeKpi (implicitly allows recording)
        // --- End Authorization ---

        // Get previous records
        $previousRecords = KpiRecord::where('employee_kpi_id', $id)
            ->orderBy('record_date', 'desc')
            ->take(5)
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'actual_value' => $record->actual_value,
                    'achievement_percentage' => $record->achievement_percentage,
                    'record_date' => $record->record_date,
                    'points_earned' => $record->points_earned,
                    'comments' => $record->comments,
                ];
            });
        
        return Inertia::render('Kpis/RecordKpi', [
            'employeeKpi' => [
                'id' => $employeeKpi->id,
                'employee' => [
                    'id' => $employeeKpi->employee->id,
                    'name' => $employeeKpi->employee->full_name,
                    'employee_id' => $employeeKpi->employee->employee_id,
                ],
                'kpi' => [
                    'id' => $employeeKpi->kpi->id,
                    'name' => $employeeKpi->kpi->name,
                    'measurement_unit' => $employeeKpi->kpi->measurement_unit,
                    'points_value' => $employeeKpi->kpi->points_value,
                ],
                'target_value' => $employeeKpi->target_value,
                'minimum_value' => $employeeKpi->minimum_value,
                'maximum_value' => $employeeKpi->maximum_value,
                'weight' => $employeeKpi->weight,
                'start_date' => $employeeKpi->start_date,
                'end_date' => $employeeKpi->end_date,
                'status' => $employeeKpi->status,
            ],
            'previousRecords' => $previousRecords,
        ]);
    }

    /**
     * Store a newly created KPI record in storage. (Allow self-recording OR admin recording)
     */
    public function storeKpiRecord(Request $request)
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to store KPI records.');
        }
        // --- End Authentication Check ---

        // Validate the request
        $validated = $request->validate([
            'employee_kpi_id' => 'required|exists:employee_kpis,id',
            'actual_value' => 'required|numeric',
            'record_date' => 'required|date',
            'comments' => 'nullable|string',
        ]);
        $employeeKpi = EmployeeKpi::with('kpi')->findOrFail($validated['employee_kpi_id']);
        $user = Auth::user();

        // --- Authorization ---
        if ($employeeKpi->employee_id !== ($user->employee->id ?? null) && !$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        // $this->authorize('create', KpiRecord::class); // Check general permission
        // $this->authorize('update', $employeeKpi); // Check if user can update this specific EmployeeKpi
        // --- End Authorization ---

        // Recalculate achievement percentage and points earned within this method
        $actualValue = $validated['actual_value'];
        $targetValue = $employeeKpi->target_value;
        $achievementPercentage = 0;
        if ($targetValue > 0) { // Avoid division by zero
            $achievementPercentage = ($actualValue / $targetValue) * 100;
        }
        $achievementPercentage = min($achievementPercentage, 100); // Cap at 100%

        $pointsEarned = round(($achievementPercentage / 100) * $employeeKpi->kpi->points_value * $employeeKpi->weight);

        $validated['achievement_percentage'] = $achievementPercentage;
        $validated['points_earned'] = $pointsEarned;
        $validated['recorded_by'] = Auth::id();

        KpiRecord::create($validated);

        // Check if this is the last record for the period and update status if needed
        if ($employeeKpi->end_date <= now() && $employeeKpi->status === 'active') {
            $employeeKpi->status = 'completed';
            $employeeKpi->save();
        }
        
        return redirect()->route('kpis.employee-kpis')->with('success', 'KPI record added successfully.');
    }

    /**
     * Display the KPI dashboard. (Show admin view or simplified employee view)
     */
    public function dashboard()
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view the dashboard.');
        }
        // --- End Authentication Check ---

        $user = Auth::user();
        $isEmployeeOnly = !$user->hasAnyRole($this->adminRoles);
        $employeeId = $user->employee->id ?? null;

        if ($isEmployeeOnly) {
            // --- Employee Specific Dashboard Data ---
            if (!$employeeId) {
                 Log::warning("KPI Dashboard: No employee record found for user ID {$user->id}");
                 return Inertia::render('Kpis/Dashboard', [ // Or a dedicated employee dashboard view
                     'isEmployeeView' => true,
                     'myStats' => [],
                     'myRecentRecords' => [],
                     'myPerformanceTrend' => ['labels' => [], 'data' => []],
                     'myBadges' => [],
                     'availableBadges' => Badge::where('is_active', true)->orderBy('points_required')->get(), // Still pass available badges
                 ])->with('error', 'Employee data not found.');
            }

            $myStats = [
                 'active_kpis' => EmployeeKpi::where('employee_id', $employeeId)->where('status', 'active')->count(),
                 'completed_kpis' => EmployeeKpi::where('employee_id', $employeeId)->where('status', 'completed')->count(),
                 'avg_achievement' => round(KpiRecord::whereHas('employeeKpi', fn($q) => $q->where('employee_id', $employeeId))->avg('achievement_percentage') ?? 0, 2),
                 'total_points' => (int) KpiRecord::whereHas('employeeKpi', fn($q) => $q->where('employee_id', $employeeId))->sum('points_earned'), // Cast to int
            ];

            $myRecentRecords = KpiRecord::whereHas('employeeKpi', fn($q)=>$q->where('employee_id', $employeeId))
                ->with(['employeeKpi.kpi']) // Eager load KPI name
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($record) {
                     return [
                         'id' => $record->id,
                         'kpi' => $record->employeeKpi->kpi->name, // Get KPI name
                         'actual_value' => $record->actual_value,
                         'target_value' => $record->employeeKpi->target_value,
                         'achievement_percentage' => round($record->achievement_percentage, 2),
                         'record_date' => Carbon::parse($record->record_date)->format('M d, Y'),
                         'points_earned' => $record->points_earned,
                     ];
                });

            $myPerformanceTrend = $this->getOverallPerformanceTrend($employeeId); // Pass employee ID

            // Get employee's badges
            $badges = Badge::where('is_active', true)->orderBy('points_required')->get();
            $myBadges = $badges->filter(fn($badge) => $myStats['total_points'] >= $badge->points_required)->values()->all();


            return Inertia::render('Kpis/Dashboard', [ // Consider a different view Kpis/MyDashboard
                 'isEmployeeView' => true,
                 'myStats' => $myStats,
                 'myRecentRecords' => $myRecentRecords,
                 'myPerformanceTrend' => $myPerformanceTrend,
                 'myBadges' => $myBadges,
                 'availableBadges' => $badges, // Pass all available badges for reference
            ]);

        } else {
            // --- Admin Dashboard Data (Existing Logic) ---
            // $this->authorize('viewAny', Kpi::class); // General permission check

            // Get top performing employees
            $topEmployees = Employee::query() // Use query() for better chaining
                ->select('id', 'first_name', 'last_name', 'employee_id', 'department_id', 'profile_picture') // Select only needed fields
                ->with(['department:id,name']) // Select specific department fields
                ->withAvg(['kpiRecords as avg_achievement' => function ($query) { // Use withAvg for clarity
                    // Correctly calculate the average within the subquery
                    $query->select(DB::raw('AVG(achievement_percentage)'));
                }], 'achievement_percentage') // The second argument 'achievement_percentage' is not needed here when using a closure
                ->orderByDesc('avg_achievement')
                ->take(5)
                ->get()
                ->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->full_name,
                        'employee_id' => $employee->employee_id,
                        'department' => $employee->department?->name,
                        'achievement' => round($employee->avg_achievement ?? 0, 2),
                        'profile_picture' => $employee->profile_picture_url, // Use accessor if available
                    ];
                });

            // Get department performance
            $departmentPerformance = Department::query()
                ->select('id', 'name')
                ->withCount('employees')
                // Correct the withAvg subquery here as well
                ->withAvg(['kpiRecords as avg_achievement' => function ($query) {
                    $query->select(DB::raw('AVG(achievement_percentage)'));
                }], 'achievement_percentage') // The second argument 'achievement_percentage' is not needed here
                ->orderByDesc('avg_achievement')
                ->get()
                ->map(function ($department) {
                    return [
                        'id' => $department->id,
                        'name' => $department->name,
                        'employee_count' => $department->employees_count,
                        'achievement' => round($department->avg_achievement ?? 0, 2),
                    ];
                });

            $kpiPerformance = Kpi::query()
                ->select('id', 'name')
                // Correct the withAvg subquery here as well
                ->withAvg(['records as avg_achievement' => function ($query) {
                    $query->select(DB::raw('AVG(achievement_percentage)'));
                }], 'achievement_percentage') // The second argument 'achievement_percentage' is not needed here
                ->orderByDesc('avg_achievement')
                ->take(10) // Limit results if needed
                ->get()
                ->map(function ($kpi) {
                    return [
                        'id' => $kpi->id,
                        'name' => $kpi->name,
                        'achievement' => round($kpi->avg_achievement ?? 0, 2),
                    ];
                });

            $recentRecords = KpiRecord::with(['employeeKpi.employee:id,first_name,last_name', 'employeeKpi.kpi:id,name']) // Select specific fields
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'employee' => $record->employeeKpi->employee->full_name,
                        'kpi' => $record->employeeKpi->kpi->name,
                        'actual_value' => $record->actual_value,
                        'target_value' => $record->employeeKpi->target_value,
                        'achievement_percentage' => round($record->achievement_percentage, 2),
                        'record_date' => Carbon::parse($record->record_date)->format('M d, Y'),
                        'created_at' => $record->created_at->diffForHumans(), // More readable time
                    ];
                });

            $overallStats = [
                'total_kpis' => Kpi::count(),
                'active_kpis' => Kpi::where('is_active', true)->count(),
                'total_employee_kpis' => EmployeeKpi::count(),
                'active_employee_kpis' => EmployeeKpi::where('status', 'active')->count(),
                'completed_employee_kpis' => EmployeeKpi::where('status', 'completed')->count(),
                'avg_achievement' => round(KpiRecord::avg('achievement_percentage') ?? 0, 2),
            ];

            $performanceTrend = $this->getOverallPerformanceTrend(); // No employee ID for overall trend

            return Inertia::render('Kpis/Dashboard', [
                'isEmployeeView' => false,
                'topEmployees' => $topEmployees,
                'departmentPerformance' => $departmentPerformance,
                'kpiPerformance' => $kpiPerformance,
                'recentRecords' => $recentRecords,
                'overallStats' => $overallStats,
                'performanceTrend' => $performanceTrend,
            ]);
        }
    }

    /**
     * Display the KPI leaderboard. (Show admin view or simplified employee view)
     */
    public function leaderboard()
    {
        // --- Authentication Check ---
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view the leaderboard.');
        }
        // --- End Authentication Check ---

        $user = Auth::user();
        $isEmployeeOnly = !$user->hasAnyRole($this->adminRoles);
        $employeeId = $user->employee->id ?? null;

        // Get all active badges ordered by points required
        $badges = Badge::where('is_active', true)->orderBy('points_required')->get();

        // Calculate total points per employee (needed for both views)
        $employeesWithPoints = Employee::query()
            ->select('id', 'first_name', 'last_name', 'employee_id', 'department_id', 'position_id', 'profile_picture') // Select needed fields
            ->with(['department:id,name', 'position:id,title']) // Select specific relation fields
            ->withSum('kpiRecords', 'points_earned')
            // ->whereHas('kpiRecords') // Uncomment if you only want employees with points on the board
            ->orderByDesc('kpi_records_sum_points_earned')
            ->get()
            ->map(function ($employee) use ($badges) {
                $totalPoints = (int) ($employee->kpi_records_sum_points_earned ?? 0); // Cast to int
                $earnedBadges = $badges->filter(fn ($badge) => $totalPoints >= $badge->points_required);
                return [
                    'id' => $employee->id,
                    'name' => $employee->full_name,
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department?->name,
                    'department_id' => $employee->department_id, // Keep for grouping
                    'position' => $employee->position?->title,
                    'profile_picture' => $employee->profile_picture_url, // Use accessor
                    'total_points' => $totalPoints,
                    'earned_badges' => $earnedBadges->values()->all(),
                ];
            });

        if ($isEmployeeOnly) {
            // --- Employee Leaderboard View ---
            if (!$employeeId) {
                 Log::warning("KPI Leaderboard: No employee record found for user ID {$user->id}");
                 return Inertia::render('Kpis/Leaderboard', [
                     'isEmployeeView' => true,
                     'myRank' => null,
                     'myPointsData' => null,
                     'topOverall' => $employeesWithPoints->take(3), // Still show top 3
                     'availableBadges' => $badges,
                 ])->with('error', 'Employee data not found.');
            }

            $myRank = $employeesWithPoints->search(fn($e) => $e['id'] === $employeeId);
            $myRank = $myRank !== false ? $myRank + 1 : null; // Get 1-based rank

            $myPointsData = $employeesWithPoints->firstWhere('id', $employeeId);

            // Show maybe top 3 and user's position?
            $topOverall = $employeesWithPoints->take(3);

             return Inertia::render('Kpis/Leaderboard', [ // Consider Kpis/MyLeaderboard
                 'isEmployeeView' => true,
                 'myRank' => $myRank,
                 'myPointsData' => $myPointsData,
                 'topOverall' => $topOverall, // Show top few for context
                 'availableBadges' => $badges,
             ]);

        } else {
            // --- Admin Leaderboard View (Existing Logic) ---
            // $this->authorize('viewAny', Kpi::class); // General permission check

            $topOverall = $employeesWithPoints->take(10);

            // Get top 3 employees per department
            $topByDepartment = $employeesWithPoints
                ->groupBy('department_id')
                ->map(function ($employeesInDept) {
                    return $employeesInDept->take(3);
                })
                // ->sortByDesc(function ($deptGroup) { // Sort departments by highest score within the group
                //     return $deptGroup->first()['total_points'] ?? 0;
                // }) // This sorting might be complex/slow, consider if needed
                ->all();

            // Get department names for display
            $departmentIds = array_keys($topByDepartment);
            $departments = Department::whereIn('id', $departmentIds)->pluck('name', 'id');


            return Inertia::render('Kpis/Leaderboard', [
                'isEmployeeView' => false,
                'topOverall' => $topOverall,
                'topByDepartment' => $topByDepartment,
                'departments' => $departments,
                'availableBadges' => $badges,
            ]);
        }
    }


    // --- Helper function modifications ---

    /**
     * Get the current value for an employee KPI (latest record).
     */
    private function getCurrentValue($employeeKpiId)
    {
        $latestRecord = KpiRecord::where('employee_kpi_id', $employeeKpiId)
            ->orderBy('record_date', 'desc')
            ->orderBy('created_at', 'desc') // Secondary sort for same date
            ->value('actual_value'); // Get only the value

        return $latestRecord; // Returns null if no record found
    }

    /**
     * Get the achievement percentage for an employee KPI based on the latest record or provided values.
     */
    private function getAchievementPercentage($employeeKpiId, $currentValue = null, $targetValue = null)
    {
        // If currentValue is not provided, fetch it
        if ($currentValue === null) {
            $currentValue = $this->getCurrentValue($employeeKpiId);
        }

        // If targetValue is not provided, fetch it from EmployeeKpi
        if ($targetValue === null) {
             $targetValue = EmployeeKpi::where('id', $employeeKpiId)->value('target_value');
        }

        // Calculate percentage
        if ($currentValue !== null && $targetValue !== null && $targetValue > 0) {
            $percentage = ($currentValue / $targetValue) * 100;
            return round(min($percentage, 100), 2); // Cap at 100% and round
        }

        return null; // Return null if calculation is not possible
    }


    /**
     * Get performance trend data for a specific KPI, optionally filtered by employee.
     */
    private function getPerformanceTrendData($kpiId, $employeeId = null)
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();

        $query = KpiRecord::query() // Use query()
            // Ensure the selectRaw alias matches the pluck key
            ->selectRaw("DATE_FORMAT(record_date, '%Y-%m') as month_year, AVG(achievement_percentage) as avg_achievement_value")
            ->whereHas('employeeKpi', function(Builder $query) use ($kpiId, $employeeId) { // Use Builder
                $query->where('kpi_id', $kpiId);
                if ($employeeId) {
                    $query->where('employee_id', $employeeId); // Add employee filter
                }
            })
            ->where('record_date', '>=', $sixMonthsAgo)
            ->groupBy('month_year')
            ->orderBy('month_year');

        // Use the correct alias 'avg_achievement_value' for plucking
        $monthlyAverages = $query->pluck('avg_achievement_value', 'month_year');

        // Prepare labels and data for the last 6 months
        $labels = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $data[] = round($monthlyAverages->get($monthKey, 0), 2); // Default to 0 if no data for the month
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get overall performance trend data, optionally filtered by employee.
     */
    private function getOverallPerformanceTrend($employeeId = null)
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();

        $query = KpiRecord::query() // Use query()
            // Ensure the selectRaw alias matches the pluck key
            ->selectRaw("DATE_FORMAT(record_date, '%Y-%m') as month_year, AVG(achievement_percentage) as avg_achievement_value")
            ->where('record_date', '>=', $sixMonthsAgo);

        if ($employeeId) {
             $query->whereHas('employeeKpi', function(Builder $q) use ($employeeId) { // Use Builder
                 $q->where('employee_id', $employeeId);
             });
        }

        $query->groupBy('month_year')
              ->orderBy('month_year');

        // Use the correct alias 'avg_achievement_value' for plucking
        $monthlyAverages = $query->pluck('avg_achievement_value', 'month_year');

        // Prepare labels and data for the last 6 months
        $labels = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $data[] = round($monthlyAverages->get($monthKey, 0), 2); // Default to 0 if no data for the month
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

}
