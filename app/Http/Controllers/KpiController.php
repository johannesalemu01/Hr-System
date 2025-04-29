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
use Inertia\Inertia;
use Carbon\Carbon;

class KpiController extends Controller
{
    /**
     * Display a listing of KPIs.
     */
    public function index(Request $request)
    {
        // Check permissions
        $this->authorize('view kpis');

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
     * Show the form for creating a new KPI.
     */
    public function create()
    {
        // Check permissions
        $this->authorize('create kpis');
        
        // Get all departments and positions for the form
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();
        
        return Inertia::render('Kpis/Create', [
            'departments' => $departments,
            'positions' => $positions,
        ]);
    }

    /**
     * Store a newly created KPI in storage.
     */
    public function store(Request $request)
    {
        // Check permissions
        $this->authorize('create kpis');
        
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
     * Display the specified KPI.
     */
    public function show($id)
    {
        // Check permissions
        $this->authorize('view kpis');
        
        // Get the KPI with related data
        $kpi = Kpi::with(['department', 'position'])->findOrFail($id);
        
        // Get employees assigned to this KPI
        $employeeKpis = EmployeeKpi::where('kpi_id', $id)
            ->with(['employee', 'employee.department', 'employee.position'])
            ->get();
        
        // Get KPI records for this KPI
        $kpiRecords = KpiRecord::whereHas('employeeKpi', function($query) use ($id) {
            $query->where('kpi_id', $id);
        })
        ->with(['employeeKpi', 'employeeKpi.employee'])
        ->orderBy('record_date', 'desc')
        ->take(10)
        ->get();
        
        // Calculate average achievement percentage
        $avgAchievement = $kpiRecords->avg('achievement_percentage');
        
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
        
        // Get performance trend data (last 6 months)
        $trendData = $this->getPerformanceTrendData($id);
        
        return Inertia::render('Kpis/Show', [
            'kpi' => $kpiData,
            'employeeKpis' => $formattedEmployeeKpis,
            'kpiRecords' => $formattedKpiRecords,
            'stats' => [
                'avgAchievement' => round($avgAchievement, 2),
                'employeeCount' => $employeeKpis->count(),
                'recordsCount' => KpiRecord::whereHas('employeeKpi', function($query) use ($id) {
                    $query->where('kpi_id', $id);
                })->count(),
            ],
            'trendData' => $trendData,
        ]);
    }

    /**
     * Show the form for editing the specified KPI.
     */
    public function edit($id)
    {
        // Check permissions
        $this->authorize('edit kpis');
        
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
     * Update the specified KPI in storage.
     */
    public function update(Request $request, $id)
    {
        // Check permissions
        $this->authorize('edit kpis');
        
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
     * Remove the specified KPI from storage.
     */
    public function destroy($id)
    {
        // Check permissions
        $this->authorize('delete kpis');
        
        // Check if KPI is in use
        $inUse = EmployeeKpi::where('kpi_id', $id)->exists();
        
        if ($inUse) {
            return redirect()->route('kpis.index')->with('error', 'Cannot delete KPI because it is assigned to employees.');
        }
        
        // Delete the KPI
        $kpi = Kpi::findOrFail($id);
        $kpi->delete();
        
        return redirect()->route('kpis.index')->with('success', 'KPI deleted successfully.');
    }

    /**
     * Display a listing of employee KPIs.
     */
    public function employeeKpis(Request $request)
    {
        // Check permissions
        $this->authorize('view employee kpis');
        
        // Get query parameters for filtering
        $departmentId = $request->query('department_id');
        $search = $request->query('search');
        $status = $request->query('status');
        
        // Base query for employee KPIs
        $query = EmployeeKpi::with(['employee', 'employee.department', 'employee.position', 'kpi']);
        
        // Apply filters
        if ($departmentId) {
            $query->whereHas('employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        if ($search) {
            $query->whereHas('employee', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        // Get paginated results
        $employeeKpis = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($empKpi) {
                return [
                    'id' => $empKpi->id,
                    'employee' => [
                        'id' => $empKpi->employee->id,
                        'name' => $empKpi->employee->full_name,
                        'employee_id' => $empKpi->employee->employee_id,
                        'department' => $empKpi->employee->department ? $empKpi->employee->department->name : 'N/A',
                        'position' => $empKpi->employee->position ? $empKpi->employee->position->title : 'N/A',
                    ],
                    'kpi' => [
                        'id' => $empKpi->kpi->id,
                        'name' => $empKpi->kpi->name,
                        'measurement_unit' => $empKpi->kpi->measurement_unit,
                    ],
                    'target_value' => $empKpi->target_value,
                    'current_value' => $this->getCurrentValue($empKpi->id),
                    'achievement_percentage' => $this->getAchievementPercentage($empKpi->id),
                    'start_date' => $empKpi->start_date,
                    'end_date' => $empKpi->end_date,
                    'status' => $empKpi->status,
                ];
            });
        
        // Get all departments for filtering
        $departments = Department::orderBy('name')->get();
        
        return Inertia::render('Kpis/EmployeeKpis', [
            'employeeKpis' => $employeeKpis,
            'departments' => $departments,
            'statuses' => ['active', 'pending', 'completed'],
            'filters' => [
                'department_id' => $departmentId,
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Show the form for assigning a KPI to an employee.
     */
    public function assignKpi($kpi_id)
    {
        // Check permissions
        $this->authorize('create employee kpis');
        
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
     * Store a newly created employee KPI in storage.
     */
    public function storeEmployeeKpi(Request $request)
    {
        // Check permissions
        $this->authorize('create employee kpis');
        
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
     * Show the form for recording a KPI value.
     */
    public function recordKpi($id)
    {
        // Check permissions
        $this->authorize('create kpi records');
        
        // Get the employee KPI
        $employeeKpi = EmployeeKpi::with(['employee', 'kpi'])->findOrFail($id);
        
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
     * Store a newly created KPI record in storage.
     */
    public function storeKpiRecord(Request $request)
    {
        // Check permissions
        $this->authorize('create kpi records');
        
        // Validate the request
        $validated = $request->validate([
            'employee_kpi_id' => 'required|exists:employee_kpis,id',
            'actual_value' => 'required|numeric',
            'record_date' => 'required|date',
            'comments' => 'nullable|string',
        ]);
        
        // Get the employee KPI
        $employeeKpi = EmployeeKpi::with('kpi')->findOrFail($validated['employee_kpi_id']);
        
        // Calculate achievement percentage
        $achievementPercentage = ($validated['actual_value'] / $employeeKpi->target_value) * 100;
        
        // Cap at 100% if exceeding
        $achievementPercentage = min($achievementPercentage, 100);
        
        // Calculate points earned based on achievement percentage and KPI points value
        $pointsEarned = round(($achievementPercentage / 100) * $employeeKpi->kpi->points_value * $employeeKpi->weight);
        
        // Add additional fields
        $validated['achievement_percentage'] = $achievementPercentage;
        $validated['points_earned'] = $pointsEarned;
        $validated['recorded_by'] = Auth::id();
        
        // Create the KPI record
        KpiRecord::create($validated);
        
        // Check if this is the last record for the period and update status if needed
        if ($employeeKpi->end_date <= now() && $employeeKpi->status === 'active') {
            $employeeKpi->status = 'completed';
            $employeeKpi->save();
        }
        
        return redirect()->route('kpis.employee-kpis')->with('success', 'KPI record added successfully.');
    }

    /**
     * Display the KPI dashboard.
     */
    public function dashboard()
    {
        // Check permissions
        $this->authorize('view kpis');
        
        // Get top performing employees
        $topEmployees = Employee::withCount(['kpiRecords as avg_achievement' => function($query) {
                $query->select(DB::raw('coalesce(avg(achievement_percentage), 0)'));
            }])
            ->with(['department'])
            ->orderBy('avg_achievement', 'desc')
            ->take(5)
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->full_name,
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department ? $employee->department->name : 'N/A',
                    'achievement' => round($employee->avg_achievement, 2),
                    'profile_picture' => $employee->profile_picture ?? '/placeholder.svg?height=40&width=40',
                ];
            });
        
        // Get department performance
        $departmentPerformance = Department::withCount(['employees as employee_count'])
            ->withCount(['kpiRecords as avg_achievement' => function($query) {
                $query->select(DB::raw('coalesce(avg(achievement_percentage), 0)'));
            }])
            ->orderBy('avg_achievement', 'desc')
            ->get()
            ->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'employee_count' => $department->employee_count,
                    'achievement' => round($department->avg_achievement, 2),
                ];
            });
        
        // Get KPI performance by type
        $kpiPerformance = Kpi::withCount(['records as avg_achievement' => function($query) {
                $query->select(DB::raw('coalesce(avg(achievement_percentage), 0)'));
            }])
            ->orderBy('avg_achievement', 'desc')
            ->get()
            ->map(function ($kpi) {
                return [
                    'id' => $kpi->id,
                    'name' => $kpi->name,
                    'achievement' => round($kpi->avg_achievement, 2),
                ];
            });
        
        // Get recent KPI records
        $recentRecords = KpiRecord::with(['employeeKpi', 'employeeKpi.employee', 'employeeKpi.kpi'])
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
                    'achievement_percentage' => $record->achievement_percentage,
                    'record_date' => $record->record_date,
                    'created_at' => $record->created_at,
                ];
            });
        
        // Get overall KPI statistics
        $overallStats = [
            'total_kpis' => Kpi::count(),
            'active_kpis' => Kpi::where('is_active', true)->count(),
            'total_employee_kpis' => EmployeeKpi::count(),
            'active_employee_kpis' => EmployeeKpi::where('status', 'active')->count(),
            'completed_employee_kpis' => EmployeeKpi::where('status', 'completed')->count(),
            'avg_achievement' => round(KpiRecord::avg('achievement_percentage') ?? 0, 2),
        ];
        
        // Get performance trend data (last 6 months)
        $performanceTrend = $this->getOverallPerformanceTrend();
        
        return Inertia::render('Kpis/Dashboard', [
            'topEmployees' => $topEmployees,
            'departmentPerformance' => $departmentPerformance,
            'kpiPerformance' => $kpiPerformance,
            'recentRecords' => $recentRecords,
            'overallStats' => $overallStats,
            'performanceTrend' => $performanceTrend,
        ]);
    }

    /**
     * Get the current value for an employee KPI.
     */
    private function getCurrentValue($employeeKpiId)
    {
        $latestRecord = KpiRecord::where('employee_kpi_id', $employeeKpiId)
            ->orderBy('record_date', 'desc')
            ->first();
            
        return $latestRecord ? $latestRecord->actual_value : null;
    }

    /**
     * Get the achievement percentage for an employee KPI.
     */
    private function getAchievementPercentage($employeeKpiId)
    {
        $latestRecord = KpiRecord::where('employee_kpi_id', $employeeKpiId)
            ->orderBy('record_date', 'desc')
            ->first();
            
        return $latestRecord ? $latestRecord->achievement_percentage : null;
    }

    /**
     * Get performance trend data for a specific KPI.
     */
    private function getPerformanceTrendData($kpiId)
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();
        
        $records = KpiRecord::whereHas('employeeKpi', function($query) use ($kpiId) {
                $query->where('kpi_id', $kpiId);
            })
            ->where('record_date', '>=', $sixMonthsAgo)
            ->orderBy('record_date')
            ->get();
        
        // Group by month and calculate average achievement
        $monthlyData = [];
        foreach ($records as $record) {
            $month = Carbon::parse($record->record_date)->format('M Y');
            
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = [
                    'total' => 0,
                    'count' => 0,
                ];
            }
            
            $monthlyData[$month]['total'] += $record->achievement_percentage;
            $monthlyData[$month]['count']++;
        }
        
        // Calculate averages and format for chart
        $labels = [];
        $data = [];
        
        // Ensure we have data for all 6 months
        for ($i = 0; $i < 6; $i++) {
            $month = Carbon::now()->subMonths(5 - $i)->format('M Y');
            $labels[] = $month;
            
            if (isset($monthlyData[$month]) && $monthlyData[$month]['count'] > 0) {
                $data[] = round($monthlyData[$month]['total'] / $monthlyData[$month]['count'], 2);
            } else {
                $data[] = 0;
            }
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get overall performance trend data.
     */
    private function getOverallPerformanceTrend()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();
        
        $records = KpiRecord::where('record_date', '>=', $sixMonthsAgo)
            ->orderBy('record_date')
            ->get();
        
        // Group by month and calculate average achievement
        $monthlyData = [];
        foreach ($records as $record) {
            $month = Carbon::parse($record->record_date)->format('M Y');
            
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = [
                    'total' => 0,
                    'count' => 0,
                ];
            }
            
            $monthlyData[$month]['total'] += $record->achievement_percentage;
            $monthlyData[$month]['count']++;
        }
        
        // Calculate averages and format for chart
        $labels = [];
        $data = [];
        
        // Ensure we have data for all 6 months
        for ($i = 0; $i < 6; $i++) {
            $month = Carbon::now()->subMonths(5 - $i)->format('M Y');
            $labels[] = $month;
            
            if (isset($monthlyData[$month]) && $monthlyData[$month]['count'] > 0) {
                $data[] = round($monthlyData[$month]['total'] / $monthlyData[$month]['count'], 2);
            } else {
                $data[] = 0;
            }
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Display the KPI leaderboard.
     */
    public function leaderboard()
    {
        // Check permissions
        $this->authorize('view kpis');

        // Get all active badges ordered by points required
        $badges = Badge::where('is_active', true)->orderBy('points_required')->get();

        // Calculate total points per employee
        $employeesWithPoints = Employee::with(['department', 'position'])
            ->withSum('kpiRecords', 'points_earned')
            ->whereHas('kpiRecords') // Only employees with records
            ->orderBy('kpi_records_sum_points_earned', 'desc')
            ->get()
            ->map(function ($employee) use ($badges) {
                $totalPoints = $employee->kpi_records_sum_points_earned ?? 0;
                $earnedBadges = $badges->filter(function ($badge) use ($totalPoints) {
                    return $totalPoints >= $badge->points_required;
                });
                return [
                    'id' => $employee->id,
                    'name' => $employee->full_name,
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department ? $employee->department->name : 'N/A',
                    'department_id' => $employee->department_id,
                    'position' => $employee->position ? $employee->position->title : 'N/A',
                    'profile_picture' => $employee->profile_picture ?? '/placeholder.svg?height=40&width=40',
                    'total_points' => $totalPoints,
                    'earned_badges' => $earnedBadges->values()->all(), // Get badges earned
                ];
            });

        // Get top 10 overall employees
        $topOverall = $employeesWithPoints->take(10);

        // Get top 3 employees per department
        $topByDepartment = $employeesWithPoints
            ->groupBy('department_id')
            ->map(function ($employeesInDept) {
                return $employeesInDept->take(3);
            })
            ->sortBy(function ($deptGroup, $deptId) use ($employeesWithPoints) {
                // Sort departments based on the highest score within the department
                 return optional($employeesWithPoints->firstWhere('department_id', $deptId))->total_points ?? 0;
            }, SORT_REGULAR, true) // Sort descending by highest score
            ->all();


        // Get department names for display
        $departments = Department::whereIn('id', array_keys($topByDepartment))->pluck('name', 'id');


        return Inertia::render('Kpis/Leaderboard', [
            'topOverall' => $topOverall,
            'topByDepartment' => $topByDepartment,
            'departments' => $departments,
            'availableBadges' => $badges, // Pass all available badges for reference if needed
        ]);
    }
}
