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
    
    public function index(Request $request)
    {
        
        $date = $request->query('date') ? Carbon::parse($request->query('date')) : null;
        $departmentId = $request->query('department_id');
        $status = $request->query('status');
        $search = $request->query('search');
        
        
        $departments = Department::orderBy('name')->get();
        
        
        $query = Attendance::with(['employee', 'employee.department']);
        
        
        if ($date) {
            $query->whereDate('date', $date);
        }
        
        
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
        
        
        $attendanceRecords = $query->orderBy('date', 'desc')->paginate(10);

        
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
            'clock_in' => 'nullable|date',
            'clock_out' => 'nullable|date|after_or_equal:clock_in',
            'status' => 'required|in:present,late,absent',
        ]);

        Attendance::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Attendance added successfully.');
    }

    /**
     * Update the specified attendance record.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'clock_in' => 'nullable|date',
            'clock_out' => 'nullable|date|after_or_equal:clock_in',
            'status' => 'required|in:present,late,absent',
        ]);

        $attendance = Attendance::findOrFail($id);

        
        if ($request->clock_in && $request->clock_out && $request->clock_out < $request->clock_in) {
            return back()->withErrors(['clock_out' => 'Clock out must be after or equal to clock in.']);
        }

        $attendance->update([
            'employee_id' => $request->employee_id,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Attendance updated successfully.');
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->delete();

        return redirect()->back()->with('success', 'Attendance record deleted successfully.');
    }
}