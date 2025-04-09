<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display the attendance dashboard.
     */
    public function index(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user has admin privileges
        $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager']);
        
        // Get query parameters for filtering
        $date = $request->query('date') ? Carbon::parse($request->query('date')) : Carbon::today();
        $departmentId = $request->query('department_id');
        $status = $request->query('status');
        $search = $request->query('search');
        
        // Get all departments for filtering
        $departments = Department::orderBy('name')->get();
        
        // Base query for attendance records
        $query = Attendance::with(['employee', 'employee.department'])
            ->whereDate('date', $date);
        
        // If not admin, only show the user's own attendance or their subordinates
        if (!$isAdmin) {
            $employee = Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                return redirect()->back()->with('error', 'Employee record not found.');
            }
            
            // If manager, show subordinates' attendance
            if ($user->hasRole('manager')) {
                $query->whereHas('employee', function ($q) use ($employee) {
                    $q->where('manager_id', $employee->id)
                      ->orWhere('id', $employee->id);
                });
            } else {
                // Regular employee, only show own attendance
                $query->where('employee_id', $employee->id);
            }
        }
        
        // Apply filters
        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }
        
        // Paginate attendance records
        $attendanceRecords = $query->orderBy('created_at', 'desc')->paginate(10);

        // Transform attendance records for frontend
        $attendanceData = $attendanceRecords->getCollection()->map(function ($record) {
            return [
                'id' => $record->id,
                'employee' => [
                    'id' => $record->employee->id,
                    'name' => $record->employee->first_name . ' ' . $record->employee->last_name,
                ],
                'date' => $record->date,
                'clock_in' => $record->clock_in,
                'clock_out' => $record->clock_out,
                'hours_worked' => $record->hours_worked,
                'status' => $record->status,
                'notes' => $record->notes,
                'location' => $record->location,
            ];
        });
        
        // Get all employees for the add attendance form (for admin only)
        $employees = [];
        if ($isAdmin) {
            $employees = Employee::orderBy('first_name')
                ->get()
                ->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                    ];
                });
        }
        
        // Get attendance statistics
        $totalEmployees = Employee::count();
        $presentToday = Attendance::whereDate('date', $date)
            ->where('status', 'present')
            ->count();
        $lateToday = Attendance::whereDate('date', $date)
            ->where('status', 'late')
            ->count();
        $absentToday = Attendance::whereDate('date', $date)
            ->where('status', 'absent')
            ->count();
        
        // Create a new paginator instance with the transformed data
        $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $attendanceData,
            $attendanceRecords->total(),
            $attendanceRecords->perPage(),
            $attendanceRecords->currentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
        
        return Inertia::render('Attendance/index', [
            'attendanceData' => $attendanceData,
            'currentDate' => $date->format('Y-m-d'),
            'departments' => $departments,
            'employees' => $employees,
            'stats' => [
                'totalEmployees' => $totalEmployees,
                'presentToday' => $presentToday,
                'lateToday' => $lateToday,
                'absentToday' => $absentToday,
            ],
            'filters' => [
                'department_id' => $departmentId,
                'status' => $status,
                'search' => $search,
            ],
            'isAdmin' => $isAdmin,
            'attendance' => [
                'data' => $paginatedData->items(),
                'links' => $paginatedData->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginatedData->currentPage(),
                    'from' => $paginatedData->firstItem(),
                    'last_page' => $paginatedData->lastPage(),
                    'path' => $paginatedData->path(),
                    'per_page' => $paginatedData->perPage(),
                    'to' => $paginatedData->lastItem(),
                    'total' => $paginatedData->total(),
                ],
            ],
        ]);
    }
}