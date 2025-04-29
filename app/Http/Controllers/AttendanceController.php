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
        // Get query parameters for filtering
        $date = $request->query('date') ? Carbon::parse($request->query('date')) : null;
        $departmentId = $request->query('department_id');
        $status = $request->query('status');
        $search = $request->query('search');
        
        // Get all departments for filtering
        $departments = Department::orderBy('name')->get();
        
        // Base query for attendance records
        $query = Attendance::with(['employee', 'employee.department']);
        
        // Filter by date if provided
        if ($date) {
            $query->whereDate('date', $date);
        }
        
        // Apply department filter
        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }
        
        // Apply search filter
        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }
        
        // Paginate attendance records
        $attendanceRecords = $query->orderBy('date', 'desc')->paginate(10);

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

        // Get attendance statistics
        $totalEmployees = Employee::count();
        $presentToday = Attendance::whereDate('date', $date ?? Carbon::today())
            ->where('status', 'present')
            ->count();
        $lateToday = Attendance::whereDate('date', $date ?? Carbon::today())
            ->where('status', 'late')
            ->count();
        $absentToday = Attendance::whereDate('date', $date ?? Carbon::today())
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
            'currentDate' => $date ? $date->format('Y-m-d') : null,
            'departments' => $departments,
            'employees' => Employee::orderBy('first_name')->get()->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                ];
            }),
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

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
        ]);

        // Check if an attendance record already exists for the employee on the given date
        $existingRecord = Attendance::where('employee_id', $request->employee_id)
            ->whereDate('date', $request->date)
            ->first();

        if ($existingRecord) {
            return response()->json(['error' => 'Attendance record already exists for this employee on this date.'], 400);
        }

        // ...existing code to create a new attendance record...
    }
}