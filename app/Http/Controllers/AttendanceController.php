<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Log; 

class AttendanceController extends Controller
{
    
    private $adminRoles = ['super-admin', 'admin', 'hr-admin', 'manager'];

    public function index(Request $request)
    {
        $user = Auth::user();
        $isEmployeeOnly = !$user->hasAnyRole(['super-admin', 'admin', 'hr-admin', 'manager']);
        $loggedInEmployeeData = null; 

        
        $date = $request->query('date') ? Carbon::parse($request->query('date')) : Carbon::today();
        $departmentId = $request->query('department_id');
        $status = $request->query('status');
        $search = $request->query('search');


        $departments = Department::orderBy('name')->get();


        $query = Attendance::with(['employee', 'employee.department']);

        
        $query->whereDate('date', $date);

        
        if ($isEmployeeOnly) {
            $employeeId = $user->employee->id ?? null; 
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
                
                $loggedInEmployee = Employee::select('id', 'first_name', 'last_name')->find($employeeId);
                 if ($loggedInEmployee) {
                    $loggedInEmployeeData = [
                        'id' => $loggedInEmployee->id,
                        
                        'name' => trim($loggedInEmployee->first_name . ' ' . $loggedInEmployee->last_name),
                    ];
                 }
            } else {
                
                $query->whereRaw('1 = 0'); 
            }
            
            $departmentId = null;
            $search = null;
            $status = null;
        } else {
            
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


        
        if ($isEmployeeOnly) {
            $employeeId = $user->employee->id ?? null;
            $totalEmployees = $employeeId ? 1 : 0; 
            $presentToday = $employeeId ? Attendance::where('employee_id', $employeeId)->whereDate('date', $date)->where('status', 'present')->count() : 0;
            $lateToday = $employeeId ? Attendance::where('employee_id', $employeeId)->whereDate('date', $date)->where('status', 'late')->count() : 0;
            $absentToday = $employeeId ? Attendance::where('employee_id', $employeeId)->whereDate('date', $date)->where('status', 'absent')->count() : 0;
        } else {
            
            $totalEmployees = Employee::count();
            $presentToday = Attendance::whereDate('date', $date)->where('status', 'present')->count();
            $lateToday = Attendance::whereDate('date', $date)->where('status', 'late')->count();
            $absentToday = Attendance::whereDate('date', $date)->where('status', 'absent')->count();
        }


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
            'departments' => $isEmployeeOnly ? [] : $departments, 
            'employees' => $isEmployeeOnly ? [] : Employee::orderBy('first_name')->get()->map(function ($employee) {
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
                'date' => $date->format('Y-m-d'),
                'department_id' => $isEmployeeOnly ? null : $departmentId, 
                'status' => $isEmployeeOnly ? null : $status,
                'search' => $isEmployeeOnly ? null : $search,
            ],
            'attendance' => [ 
                 'data' => $attendanceRecords->items(), 
                 'links' => $attendanceRecords->linkCollection()->toArray(),
                 'meta' => [
                     'current_page' => $attendanceRecords->currentPage(),
                     'from' => $attendanceRecords->firstItem(),
                     'last_page' => $attendanceRecords->lastPage(),
                     'path' => $attendanceRecords->path(),
                     'per_page' => $attendanceRecords->perPage(),
                     'to' => $attendanceRecords->lastItem(),
                     'total' => $attendanceRecords->total(),
                 ],
             ],
             'isEmployeeView' => $isEmployeeOnly, 
             'loggedInEmployeeData' => $loggedInEmployeeData, 
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isEmployeeOnly = !$user->hasAnyRole($this->adminRoles);

        
        $timeFormat = $isEmployeeOnly ? 'H:i' : 'Y-m-d\TH:i';
        
        $timeValidationRule = ['nullable', "date_format:{$timeFormat}"];

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            
            'date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('attendances')->where(function ($query) use ($request) {
                    return $query->where('employee_id', $request->input('employee_id'));
                }),
            ],
            'clock_in' => $timeValidationRule, 
            
            'clock_out' => [
                'nullable',
                "date_format:{$timeFormat}",
                Rule::when(!empty($request->input('clock_in')), 'after_or_equal:clock_in')
            ],
            'status' => 'required|in:present,late,absent',
        ], [
            
            'date.unique' => 'An attendance record already exists for this employee on this date.',
        ]);

        
        $attendanceDate = Carbon::parse($validated['date']);
        $clockInDateTime = null;
        $clockOutDateTime = null;

        try {
            if (!empty($validated['clock_in'])) {
                $clockInTime = Carbon::parse($validated['clock_in']); 
                $clockInDateTime = $attendanceDate->copy()->setTime($clockInTime->hour, $clockInTime->minute, $clockInTime->second);
            }

            if (!empty($validated['clock_out'])) {
                $clockOutTime = Carbon::parse($validated['clock_out']); 
                $clockOutDateTime = $attendanceDate->copy()->setTime($clockOutTime->hour, $clockOutTime->minute, $clockOutTime->second);

                
                if ($clockInDateTime && $clockOutDateTime && $clockOutDateTime->isBefore($clockInDateTime)) {
                    return back()->withErrors(['clock_out' => 'Clock out time must be after clock in time on the selected date.'])->withInput();
                }
            }
        } catch (\Exception $e) {
             Log::error("Error parsing date/time in AttendanceController@store: " . $e->getMessage());
             return back()->withErrors(['clock_in' => 'Invalid time format provided.'])->withInput();
        }
        

        
        $hoursWorked = null;
        if ($clockInDateTime && $clockOutDateTime) {
            $hoursWorked = $clockInDateTime->diffInHours($clockOutDateTime); 
        }

        Attendance::create([
            'employee_id' => $validated['employee_id'],
            'date' => $attendanceDate->format('Y-m-d'),
            'clock_in' => $clockInDateTime,
            'clock_out' => $clockOutDateTime,
            'status' => $validated['status'],
            'hours_worked' => $hoursWorked, 
        ]);

        return redirect()->back()->with('success', 'Attendance added successfully.');
    }

    /**
     * Update the specified attendance record.
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $user = Auth::user(); 

        
        if ($attendance->employee_id !== ($user->employee->id ?? null) && !$user->hasAnyRole($this->adminRoles)) {
            abort(403, 'Unauthorized action.');
        }
        

        
        $timeFormat = 'Y-m-d\TH:i';
        
        $timeValidationRule = ['nullable', "date_format:{$timeFormat}"];

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            
            'date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('attendances')->where(function ($query) use ($request) {
                    return $query->where('employee_id', $request->input('employee_id'));
                })->ignore($id), 
            ],
            'clock_in' => $timeValidationRule, 
            
            'clock_out' => [
                'nullable',
                "date_format:{$timeFormat}",
                Rule::when(!empty($request->input('clock_in')), 'after_or_equal:clock_in')
            ],
            'status' => 'required|in:present,late,absent',
        ], [
            'date.unique' => 'An attendance record already exists for this employee on this date.',
        ]);

        
        $attendanceDate = Carbon::parse($validated['date']);
        $clockInDateTime = null;
        $clockOutDateTime = null;

        try {
            if (!empty($validated['clock_in'])) {
                $clockInTime = Carbon::parse($validated['clock_in']);
                $clockInDateTime = $attendanceDate->copy()->setTime($clockInTime->hour, $clockInTime->minute, $clockInTime->second);
            }

            if (!empty($validated['clock_out'])) {
                $clockOutTime = Carbon::parse($validated['clock_out']);
                $clockOutDateTime = $attendanceDate->copy()->setTime($clockOutTime->hour, $clockOutTime->minute, $clockOutTime->second);

                if ($clockInDateTime && $clockOutDateTime && $clockOutDateTime->isBefore($clockInDateTime)) {
                    return back()->withErrors(['clock_out' => 'Clock out time must be after clock in time on the selected date.'])->withInput();
                }
            }
        } catch (\Exception $e) {
             Log::error("Error parsing date/time in AttendanceController@update: " . $e->getMessage());
             return back()->withErrors(['clock_in' => 'Invalid time format provided.'])->withInput();
        }
        

        
        $hoursWorked = null;
        if ($clockInDateTime && $clockOutDateTime) {
            $hoursWorked = $clockInDateTime->diffInHours($clockOutDateTime);
        }

        $attendance->update([
            'employee_id' => $validated['employee_id'],
            'date' => $attendanceDate->format('Y-m-d'),
            'clock_in' => $clockInDateTime,
            'clock_out' => $clockOutDateTime,
            'status' => $validated['status'],
            'hours_worked' => $hoursWorked, 
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