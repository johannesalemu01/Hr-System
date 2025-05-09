<?php

namespace App\Http\Controllers;

use App\Models\Kpi;
use App\Models\Employee;
use App\Models\EmployeeKpi;
use App\Models\KpiRecord;
use App\Models\Department;
use App\Models\Position;
use App\Models\Badge; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Http\RedirectResponse; 
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder; 

class KpiController extends Controller
{
    
    private $adminRoles = ['super-admin', 'admin', 'hr-admin', 'manager'];

    /**
     * Display a listing of KPIs. (Admin/Manager only)
     */
    public function index(Request $request)
    {
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view KPIs.');
        }
        

        $user = Auth::user();
        
        if (!$user->hasAnyRole($this->adminRoles)) {
             return redirect()->route('kpis.employee-kpis')->with('message', 'Viewing your assigned KPIs.'); 
        }
        

        
        

        
        $departmentId = $request->query('department_id');
        $search = $request->query('search');
        $status = $request->query('status');
        
        
        $query = Kpi::with(['department', 'position']);
        
        
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
        
        
        $departments = Department::orderBy('name')->get();
        
        
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
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to create KPIs.');
        }
        

        $user = Auth::user();
        
        if (!$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        
        

        
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();

        // Fetch employees for assignment
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

        return Inertia::render('Kpis/Create', [
            'departments' => $departments,
            'positions' => $positions,
            'employees' => $employees,
        ]);
    }

    /**
     * Store a newly created KPI in storage. (Admin/Manager only)
     */
    public function store(Request $request)
    {
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to store KPIs.');
        }
        

        $user = Auth::user();
        
        if (!$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        
        

        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'measurement_unit' => 'required|string|max:50',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,annually',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'is_active' => 'boolean',
            'points_value' => 'required|integer|min:1|max:100',
            // Assignment fields are validated below if present
        ]);

        $kpi = Kpi::create($validated);

        // Assign KPI to employee if assign_employee_id is present
        if ($request->filled('assign_employee_id')) {
            $assignValidated = $request->validate([
                'assign_target_value' => 'required|numeric',
                'assign_minimum_value' => 'required|numeric|lte:assign_target_value',
                'assign_maximum_value' => 'required|numeric|gte:assign_target_value',
                'assign_weight' => 'required|numeric|min:0.1|max:5',
                'assign_start_date' => 'required|date',
                'assign_end_date' => 'required|date|after:assign_start_date',
            ]);

            EmployeeKpi::create([
                'employee_id' => $request->assign_employee_id,
                'kpi_id' => $kpi->id,
                'target_value' => $assignValidated['assign_target_value'],
                'minimum_value' => $assignValidated['assign_minimum_value'],
                'maximum_value' => $assignValidated['assign_maximum_value'],
                'weight' => $assignValidated['assign_weight'],
                'start_date' => $assignValidated['assign_start_date'],
                'end_date' => $assignValidated['assign_end_date'],
                'status' => 'active',
                'assigned_by' => Auth::id(),
            ]);
        }

        return redirect()->route('kpis.index')->with('success', 'KPI created successfully.');
    }

    /**
     * Display the specified KPI. (Filter for employee role)
     */
    public function show($id)
    {
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view this KPI.');
        }
        

        $user = Auth::user();
        $kpi = Kpi::with(['department', 'position'])->findOrFail($id);
        $isEmployeeOnly = !$user->hasAnyRole($this->adminRoles);
        $employeeId = $user->employee->id ?? null;

        
        if ($isEmployeeOnly) {
            if (!$employeeId) {
                abort(403, 'Employee record not found for user.');
            }
            
            $isAssigned = EmployeeKpi::where('kpi_id', $id)
                                     ->where('employee_id', $employeeId)
                                     ->exists();
            if (!$isAssigned) {
                 abort(403, 'You are not assigned this KPI.');
            }
            
             $employeeKpis = EmployeeKpi::where('kpi_id', $id)
                ->where('employee_id', $employeeId) 
                ->with(['employee', 'employee.department', 'employee.position']) 
                ->get();

             $kpiRecords = KpiRecord::whereHas('employeeKpi', function($query) use ($id, $employeeId) {
                 $query->where('kpi_id', $id)->where('employee_id', $employeeId); 
             })
             ->with(['employeeKpi.employee']) 
             ->orderBy('record_date', 'desc')
             ->take(10) 
             ->get();

        } else {
            
            

            $employeeKpis = EmployeeKpi::where('kpi_id', $id)
                ->with(['employee', 'employee.department', 'employee.position'])
                ->get();

            $kpiRecords = KpiRecord::whereHas('employeeKpi', function($query) use ($id) {
                $query->where('kpi_id', $id);
            })
            ->with(['employeeKpi.employee'])
            ->orderBy('record_date', 'desc')
            ->take(10) 
            ->get();
        }
        


        
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
        
        
        $avgAchievement = $kpiRecords->avg('achievement_percentage');

        
        $trendData = $this->getPerformanceTrendData($id, $isEmployeeOnly ? $employeeId : null);

        return Inertia::render('Kpis/Show', [
            'kpi' => $kpiData,
            'employeeKpis' => $formattedEmployeeKpis,
            'kpiRecords' => $formattedKpiRecords,
            'stats' => [
                'avgAchievement' => round($avgAchievement, 2),
                'employeeCount' => $employeeKpis->count(), 
                'recordsCount' => $kpiRecords->count(), 
            ],
            'trendData' => $trendData,
            'isEmployeeView' => $isEmployeeOnly, 
        ]);
    }

    /**
     * Show the form for editing the specified KPI. (Admin/Manager only)
     */
    public function edit($id)
    {
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to edit KPIs.');
        }
        

        $user = Auth::user();
         
        if (!$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        
        
        

        
        $kpi = Kpi::with(['department', 'position'])->findOrFail($id);
        
        
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

        
        $stats = [
            'avgAchievement' => round($kpiRecords->avg('achievement_percentage'), 2),
            'employeeCount' => $employeeKpis->count(),
        ];

        
        $trendData = $this->getPerformanceTrendData($id);

        
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
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to update KPIs.');
        }
        

        $user = Auth::user();
         
        if (!$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        
        
        

        
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
        
        
        $kpi = Kpi::findOrFail($id);
        $kpi->update($validated);
        
        return redirect()->route('kpis.index')->with('success', 'KPI updated successfully.');
    }

    /**
     * Remove the specified KPI from storage. (Admin/Manager only)
     */
    public function destroy($id): RedirectResponse
    {
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to delete KPIs.');
        }
        

        $user = Auth::user();
         
        if (!$user->hasAnyRole($this->adminRoles)) {
             return redirect()->back()->with('error', 'You do not have permission to delete KPIs.');
        }
        
        
        

        Log::info("Attempting to delete KPI with ID: {$id}");

        try {
            
            $kpi = Kpi::find($id);

            if (!$kpi) {
                Log::warning("KPI not found for ID: {$id}");
                
                return redirect()->back()->with('error', 'KPI not found.');
            }
            Log::info("KPI found: " . $kpi->name);


            
            // SAFELY delete all EmployeeKpi assignments for this KPI before deleting the KPI itself
            \App\Models\EmployeeKpi::where('kpi_id', $id)->delete();

            // Optionally, delete all KPI records related to this KPI (if you want to clean up all related data)
            // \App\Models\KpiRecord::whereHas('employeeKpi', function($q) use ($id) {
            //     $q->where('kpi_id', $id);
            // })->delete();

            // Now delete the KPI
            Log::info("Proceeding to delete KPI ID: {$id}");
            $deleted = $kpi->delete();

            if ($deleted) {
                Log::info("Successfully deleted KPI ID: {$id}");
                
                return redirect()->route('kpis.index')->with('success', 'KPI deleted successfully.');
            } else {
                Log::error("Failed to delete KPI ID: {$id} from database.");
                 
                return redirect()->back()->with('error', 'Failed to delete KPI from database.');
            }

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::error("Authorization failed for deleting KPI ID: {$id}. Error: " . $e->getMessage());
             
            return redirect()->back()->with('error', 'You do not have permission to delete KPIs.');
        } catch (\Exception $e) {
            Log::error("An error occurred while deleting KPI ID: {$id}. Error: " . $e->getMessage());
             
            return redirect()->back()->with('error', 'An unexpected error occurred while deleting the KPI.');
        }
    }

    /**
     * Display a listing of employee KPIs. Filter for employee role.
     */
    public function employeeKpis(Request $request)
    {
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view employee KPIs.');
        }
        

        $user = Auth::user();
        $isEmployeeOnly = !$user->hasAnyRole($this->adminRoles);
        $employeeId = $user->employee->id ?? null;

        
        $query = EmployeeKpi::with(['employee', 'employee.department', 'employee.position', 'kpi']);

        
        if ($isEmployeeOnly) {
            if (!$employeeId) {
                 $query->whereRaw('1 = 0'); 
                 Log::warning("EmployeeKPIs: No employee record found for user ID {$user->id}");
            } else {
                $query->where('employee_id', $employeeId);
            }
            
            $departmentId = null;
            $search = null;
            $status = null;
        } else {
            
            

            $departmentId = $request->query('department_id');
            $search = $request->query('search');
            $status = $request->query('status');

            if ($departmentId) {
                $query->whereHas('employee', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }
            if ($search) {
                 $query->where(function(Builder $q) use ($search) { 
                     $q->whereHas('employee', function(Builder $subQ) use ($search) { 
                         $subQ->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                              ->orWhere('employee_id', 'like', "%{$search}%");
                     })->orWhereHas('kpi', function(Builder $subQ) use ($search) { 
                         $subQ->where('name', 'like', "%{$search}%");
                     });
                 });
            }
            if ($status) {
                $query->where('status', $status);
            }
        }
        

        
        $employeeKpis = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($empKpi) {
                
                $currentValue = $this->getCurrentValue($empKpi->id);
                $achievementPercentage = $this->getAchievementPercentage($empKpi->id, $currentValue, $empKpi->target_value); 

                return [
                    'id' => $empKpi->id,
                    'employee' => [
                        'id' => $empKpi->employee->id,
                        'name' => $empKpi->employee->full_name,
                        'employee_id' => $empKpi->employee->employee_id,
                        'department' => $empKpi->employee->department?->name, 
                        'position' => $empKpi->employee->position?->title, 
                    ],
                    'kpi' => [
                        'id' => $empKpi->kpi->id,
                        'name' => $empKpi->kpi->name,
                        'measurement_unit' => $empKpi->kpi->measurement_unit,
                    ],
                    'target_value' => $empKpi->target_value,
                    'current_value' => $currentValue, 
                    'achievement_percentage' => $achievementPercentage, 
                    'start_date' => $empKpi->start_date,
                    'end_date' => $empKpi->end_date,
                    'status' => $empKpi->status,
                ];
            });

        
        $departments = $isEmployeeOnly ? [] : Department::orderBy('name')->get();

        // Fetch all KPIs for assignment dropdown (so new KPIs are always available for assignment)
        $allKpis = Kpi::orderBy('name')->get(['id', 'name']);

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
            'allKpis' => $allKpis, // <-- add this line
        ]);
    }

    /**
     * Show the form for assigning a KPI to an employee. (Admin/Manager only)
     */
    public function assignKpi($kpi_id)
    {
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to assign KPIs.');
        }
        

        $user = Auth::user();
         
        if (!$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        
        

        
        $kpi = Kpi::findOrFail($kpi_id);

        
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
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to assign KPIs.');
        }
        

        $user = Auth::user();
         
        if (!$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        
        

        
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
        
        
        $validated['status'] = 'active';
        $validated['assigned_by'] = Auth::id();
        
        
        EmployeeKpi::create($validated);
        
        return redirect()->route('kpis.employee-kpis')->with('success', 'KPI assigned to employee successfully.');
    }

    /**
     * Show the form for recording a KPI value. (Allow self-recording OR admin recording)
     */
    public function recordKpi($id)
    {
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to record KPI values.');
        }
        

        $employeeKpi = EmployeeKpi::with(['employee', 'kpi'])->findOrFail($id);
        $user = Auth::user();

        
        if ($employeeKpi->employee_id !== ($user->employee->id ?? null) && !$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        
        
        

        
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
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to store KPI records.');
        }
        

        
        $validated = $request->validate([
            'employee_kpi_id' => 'required|exists:employee_kpis,id',
            'actual_value' => 'required|numeric',
            'record_date' => 'required|date',
            'comments' => 'nullable|string',
        ]);
        $employeeKpi = EmployeeKpi::with('kpi')->findOrFail($validated['employee_kpi_id']);
        $user = Auth::user();

        
        if ($employeeKpi->employee_id !== ($user->employee->id ?? null) && !$user->hasAnyRole($this->adminRoles)) {
             abort(403, 'Unauthorized action.');
        }
        
        
        

        
        $actualValue = $validated['actual_value'];
        $targetValue = $employeeKpi->target_value;
        $achievementPercentage = 0;
        if ($targetValue > 0) { 
            $achievementPercentage = ($actualValue / $targetValue) * 100;
        }
        $achievementPercentage = min($achievementPercentage, 100); 

        $pointsEarned = round(($achievementPercentage / 100) * $employeeKpi->kpi->points_value * $employeeKpi->weight);

        $validated['achievement_percentage'] = $achievementPercentage;
        $validated['points_earned'] = $pointsEarned;
        $validated['recorded_by'] = Auth::id();

        KpiRecord::create($validated);

        
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
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view the dashboard.');
        }
        

        $user = Auth::user();
        $isEmployeeOnly = !$user->hasAnyRole($this->adminRoles);
        $employeeId = $user->employee->id ?? null;

        if ($isEmployeeOnly) {
            
            if (!$employeeId) {
                 Log::warning("KPI Dashboard: No employee record found for user ID {$user->id}");
                 return Inertia::render('Kpis/Dashboard', [ 
                     'isEmployeeView' => true,
                     'myStats' => [],
                     'myRecentRecords' => [],
                     'myPerformanceTrend' => ['labels' => [], 'data' => []],
                     'myBadges' => [],
                     'availableBadges' => Badge::where('is_active', true)->orderBy('points_required')->get(), 
                 ])->with('error', 'Employee data not found.');
            }

            $myStats = [
                 'active_kpis' => EmployeeKpi::where('employee_id', $employeeId)->where('status', 'active')->count(),
                 'completed_kpis' => EmployeeKpi::where('employee_id', $employeeId)->where('status', 'completed')->count(),
                 'avg_achievement' => round(KpiRecord::whereHas('employeeKpi', fn($q) => $q->where('employee_id', $employeeId))->avg('achievement_percentage') ?? 0, 2),
                 'total_points' => (int) KpiRecord::whereHas('employeeKpi', fn($q) => $q->where('employee_id', $employeeId))->sum('points_earned'), 
            ];

            $myRecentRecords = KpiRecord::whereHas('employeeKpi', fn($q)=>$q->where('employee_id', $employeeId))
                ->with(['employeeKpi.kpi']) 
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($record) {
                     return [
                         'id' => $record->id,
                         'kpi' => $record->employeeKpi->kpi->name, 
                         'actual_value' => $record->actual_value,
                         'target_value' => $record->employeeKpi->target_value,
                         'achievement_percentage' => round($record->achievement_percentage, 2),
                         'record_date' => Carbon::parse($record->record_date)->format('M d, Y'),
                         'points_earned' => $record->points_earned,
                     ];
                });

            $myPerformanceTrend = $this->getOverallPerformanceTrend($employeeId); 

            
            $badges = Badge::where('is_active', true)->orderBy('points_required')->get();
            $myBadges = $badges->filter(fn($badge) => $myStats['total_points'] >= $badge->points_required)->values()->all();


            return Inertia::render('Kpis/Dashboard', [ 
                 'isEmployeeView' => true,
                 'myStats' => $myStats,
                 'myRecentRecords' => $myRecentRecords,
                 'myPerformanceTrend' => $myPerformanceTrend,
                 'myBadges' => $myBadges,
                 'availableBadges' => $badges, 
            ]);

        } else {
            
            

            
            $topEmployees = Employee::query() 
                ->select('id', 'first_name', 'last_name', 'employee_id', 'department_id', 'profile_picture') 
                ->with(['department:id,name']) 
                ->withAvg(['kpiRecords as avg_achievement' => function ($query) { 
                    
                    $query->select(DB::raw('AVG(achievement_percentage)'));
                }], 'achievement_percentage') 
                ->orderByDesc('avg_achievement')
                ->take(5)
                ->get()
                ->map(function ($employee) {
                    
                    $profilePictureUrl = $employee->profile_picture && Storage::disk('public')->exists($employee->profile_picture)
                        ? asset('storage/' . $employee->profile_picture)
                        : asset('images/default-avatar.png'); 

                    return [
                        'id' => $employee->id,
                        'name' => $employee->full_name, 
                        'employee_id' => $employee->employee_id,
                        'department' => $employee->department?->name,
                        'achievement' => round($employee->avg_achievement ?? 0, 2),
                        'profile_picture' => $profilePictureUrl, 
                    ];
                });

            
            $departmentPerformance = Department::withCount('employees')
                ->get()
                ->map(function ($department) {
                    // Calculate average achievement for all KPI records of employees in this department
                    $avgAchievement = \App\Models\KpiRecord::whereHas('employeeKpi.employee', function ($q) use ($department) {
                            $q->where('department_id', $department->id);
                        })
                        ->avg('achievement_percentage');

                    return [
                        'id' => $department->id,
                        'name' => $department->name,
                        'employee_count' => $department->employees_count,
                        'achievement' => round($avgAchievement ?? 0, 2),
                    ];
                });

            $kpiPerformance = Kpi::query()
                ->select('id', 'name')
                
                ->withAvg(['records as avg_achievement' => function ($query) {
                    $query->select(DB::raw('AVG(achievement_percentage)'));
                }], 'achievement_percentage') 
                ->orderByDesc('avg_achievement')
                ->take(10) 
                ->get()
                ->map(function ($kpi) {
                    return [
                        'id' => $kpi->id,
                        'name' => $kpi->name,
                        'achievement' => round($kpi->avg_achievement ?? 0, 2),
                    ];
                });

            $recentRecords = KpiRecord::with(['employeeKpi.employee:id,first_name,last_name', 'employeeKpi.kpi:id,name']) 
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
                        'created_at' => $record->created_at->diffForHumans(), 
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

            $performanceTrend = $this->getOverallPerformanceTrend(); 

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
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view the leaderboard.');
        }
        

        $user = Auth::user();
        $isEmployeeOnly = !$user->hasAnyRole($this->adminRoles);
        $employeeId = $user->employee->id ?? null;

        
        $badges = Badge::where('is_active', true)->orderBy('points_required')->get();

        
        $employeesWithPoints = Employee::query()
            ->select('id', 'first_name', 'last_name', 'employee_id', 'department_id', 'position_id', 'profile_picture') 
            ->with(['department:id,name', 'position:id,title']) 
            ->withSum('kpiRecords', 'points_earned')
            
            ->orderByDesc('kpi_records_sum_points_earned')
            ->get()
            ->map(function ($employee) use ($badges) {
                $totalPoints = (int) ($employee->kpi_records_sum_points_earned ?? 0); 
                $earnedBadges = $badges->filter(fn ($badge) => $totalPoints >= $badge->points_required);

                
                $profilePictureUrl = $employee->profile_picture && Storage::disk('public')->exists($employee->profile_picture)
                    ? asset('storage/' . $employee->profile_picture)
                    : asset('images/default-avatar.png'); 

                return [
                    'id' => $employee->id,
                    'name' => $employee->full_name, 
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department?->name,
                    'department_id' => $employee->department_id, 
                    'position' => $employee->position?->title,
                    'profile_picture' => $profilePictureUrl, 
                    'total_points' => $totalPoints,
                    'earned_badges' => $earnedBadges->values()->all(),
                ];
            });

        if ($isEmployeeOnly) {
            
            if (!$employeeId) {
                 Log::warning("KPI Leaderboard: No employee record found for user ID {$user->id}");
                 return Inertia::render('Kpis/Leaderboard', [
                     'isEmployeeView' => true,
                     'myRank' => null,
                     'myPointsData' => null,
                     'topOverall' => $employeesWithPoints->take(3), 
                     'availableBadges' => $badges,
                 ])->with('error', 'Employee data not found.');
            }

            $myRank = $employeesWithPoints->search(fn($e) => $e['id'] === $employeeId);
            $myRank = $myRank !== false ? $myRank + 1 : null; 

            $myPointsData = $employeesWithPoints->firstWhere('id', $employeeId);

            
            $topOverall = $employeesWithPoints->take(3);

             return Inertia::render('Kpis/Leaderboard', [ 
                 'isEmployeeView' => true,
                 'myRank' => $myRank,
                 'myPointsData' => $myPointsData,
                 'topOverall' => $topOverall, 
                 'availableBadges' => $badges,
             ]);

        } else {
            
            

            $topOverall = $employeesWithPoints->take(10);

            
            $topByDepartment = $employeesWithPoints
                ->groupBy('department_id')
                ->map(function ($employeesInDept) {
                    return $employeesInDept->take(3);
                })
                
                
                
                ->all();

            
            $departmentIds = array_keys($topByDepartment);
            $departments = Department::whereIn('id', $departmentIds)->pluck('name', 'id');


            return Inertia::render('Kpis/AdminLeaderboard', [ // Changed to AdminLeaderboard
                'isEmployeeView' => false,
                'topOverall' => $topOverall,
                'topByDepartment' => $topByDepartment,
                'departments' => $departments,
                'availableBadges' => $badges,
            ]);
        }
    }


    

    /**
     * Get the current value for an employee KPI (latest record).
     */
    private function getCurrentValue($employeeKpiId)
    {
        $latestRecord = KpiRecord::where('employee_kpi_id', $employeeKpiId)
            ->orderBy('record_date', 'desc')
            ->orderBy('created_at', 'desc') 
            ->value('actual_value'); 

        return $latestRecord; 
    }

    /**
     * Get the achievement percentage for an employee KPI based on the latest record or provided values.
     */
    private function getAchievementPercentage($employeeKpiId, $currentValue = null, $targetValue = null)
    {
        
        if ($currentValue === null) {
            $currentValue = $this->getCurrentValue($employeeKpiId);
        }

        
        if ($targetValue === null) {
             $targetValue = EmployeeKpi::where('id', $employeeKpiId)->value('target_value');
        }

        
        if ($currentValue !== null && $targetValue !== null && $targetValue > 0) {
            $percentage = ($currentValue / $targetValue) * 100;
            return round(min($percentage, 100), 2); 
        }

        return null; 
    }


    /**
     * Get performance trend data for a specific KPI, optionally filtered by employee.
     */
    private function getPerformanceTrendData($kpiId, $employeeId = null)
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();

        $query = KpiRecord::query() 
            
            ->selectRaw("DATE_FORMAT(record_date, '%Y-%m') as month_year, AVG(achievement_percentage) as avg_achievement_value")
            ->whereHas('employeeKpi', function(Builder $query) use ($kpiId, $employeeId) { 
                $query->where('kpi_id', $kpiId);
                if ($employeeId) {
                    $query->where('employee_id', $employeeId); 
                }
            })
            ->where('record_date', '>=', $sixMonthsAgo)
            ->groupBy('month_year')
            ->orderBy('month_year');

        
        $monthlyAverages = $query->pluck('avg_achievement_value', 'month_year');

        
        $labels = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $data[] = round($monthlyAverages->get($monthKey, 0), 2); 
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

        $query = KpiRecord::query() 
            
            ->selectRaw("DATE_FORMAT(record_date, '%Y-%m') as month_year, AVG(achievement_percentage) as avg_achievement_value")
            ->where('record_date', '>=', $sixMonthsAgo);

        if ($employeeId) {
             $query->whereHas('employeeKpi', function(Builder $q) use ($employeeId) { 
                 $q->where('employee_id', $employeeId);
             });
        }

        $query->groupBy('month_year')
              ->orderBy('month_year');

        
        $monthlyAverages = $query->pluck('avg_achievement_value', 'month_year');

        
        $labels = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $data[] = round($monthlyAverages->get($monthKey, 0), 2); 
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

}
