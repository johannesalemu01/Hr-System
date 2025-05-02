<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon; 

class LeaveRequestController extends Controller
{

    public function index(Request $request)
    {
        
        $user = Auth::user();

        
        $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager', 'admin']);

        
        $employee = !$isAdmin ? Employee::where('user_id', $user->id)->first() : null;

        if (!$isAdmin && !$employee) {
            
            return redirect()->route('dashboard')->with('error', 'You are not associated with an employee record. Please contact the administrator.');
        }

        
        $leaveTypes = LeaveType::where('is_active', true)->get();

        
        $departments = Department::orderBy('name')->get();

        
        $status = $request->query('status');
        $departmentId = $request->query('department_id');
        $leaveTypeId = $request->query('leave_type_id');
        $search = $request->query('search');

        
        $query = LeaveRequest::with(['employee', 'leaveType', 'employee.department']);

        if (!$isAdmin) {
            
            $query->where('employee_id', $employee->id);
        }

        
        if ($status) {
            $query->where('status', $status);
        }

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($leaveTypeId) {
            $query->where('leave_type_id', $leaveTypeId);
        }

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        
        $leaveRequests = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($request) {
                return [
                    'id' => $request->id,
                    'employee' => [
                        'id' => $request->employee->id,
                        'name' => $request->employee->first_name . ' ' . $request->employee->last_name,
                        'employee_id' => $request->employee->employee_id,
                        'department' => $request->employee->department ? $request->employee->department->name : 'N/A',
                    ],
                    'type' => $request->leaveType->name,
                    'leave_type_id' => $request->leave_type_id, 
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'total_days' => $request->total_days,
                    'reason' => $request->reason,
                    'status' => $request->status,
                    'rejection_reason' => $request->rejection_reason,
                    'created_at' => $request->created_at,
                ];
            });

        
        $employees = $isAdmin ? Employee::with('department')->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'employee_id' => $employee->employee_id,
                'department' => $employee->department ? $employee->department->name : 'N/A',
            ];
        }) : [];

        
        $pendingLeaveRequestsCount = LeaveRequest::where('status', 'pending')->count();

        return Inertia::render('Leave/index', [
            'leaveTypes' => $leaveTypes,
            'leaveRequests' => $leaveRequests,
            'departments' => $departments,
            'filters' => [
                'status' => $status,
                'department_id' => $departmentId,
                'leave_type_id' => $leaveTypeId,
                'search' => $search,
            ],
            'employees' => $employees, 
            'isAdmin' => $isAdmin, 
            'pendingLeaveRequestsCount' => $pendingLeaveRequestsCount,
        ]);
    }

    /**
     * Store a newly created leave request.
     */
    public function store(Request $request)
    {
        
        $user = Auth::user();
        
        
        $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager', 'admin']);
        
        
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'employee_id' => $isAdmin ? 'required|exists:employees,id' : 'sometimes',
        ]);
        
        
        $employeeId = null;
        
        if ($isAdmin && isset($validated['employee_id'])) {
            
            $employeeId = $validated['employee_id'];
        } else {
            
            $employee = Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                return redirect()->back()->with('error', 'Employee record not found.');
            }
            
            $employeeId = $employee->id;
        }
        
        
        
        $totalDays = $this->calculateLeaveDays($validated['start_date'], $validated['end_date']);
        
        
        $leaveRequest = new LeaveRequest();
        $leaveRequest->employee_id = $employeeId;
        $leaveRequest->leave_type_id = $validated['leave_type_id'];
        $leaveRequest->start_date = $validated['start_date'];
        $leaveRequest->end_date = $validated['end_date'];
        $leaveRequest->total_days = $totalDays;
        $leaveRequest->reason = $validated['reason'];
        $leaveRequest->status = 'pending';
        $leaveRequest->save();
        
        return redirect()->route('leave.index')->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     * (Optional: Usually handled by the modal in Inertia)
     */
    
    
    
    

    /**
     * Update the specified leave request in storage.
     */
    public function update(Request $request, LeaveRequest $leave) 
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager', 'admin']);

        if (!$isAdmin) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }

        

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        
        $totalDays = $this->calculateLeaveDays($validated['start_date'], $validated['end_date']);

        
        $leave->leave_type_id = $validated['leave_type_id'];
        $leave->start_date = $validated['start_date'];
        $leave->end_date = $validated['end_date'];
        $leave->total_days = $totalDays;
        $leave->reason = $validated['reason'];

        
        

        $leave->save();

        return redirect()->route('leave.index')->with('success', 'Leave request updated successfully.');
    }


    /**
     * Update the status of a leave request.
     */
    public function updateStatus(Request $request, LeaveRequest $leave) 
    {
        
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500',
        ]);
        
        
        $user = Auth::user();
        
        
        $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager', 'admin']);

        if (!$isAdmin) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }

        
        $leave->status = $validated['status'];
        $leave->rejection_reason = $validated['status'] === 'rejected' ? $validated['rejection_reason'] : null;
        $leave->approved_by = $user->id;
        $leave->approved_at = now();
        $leave->save();

        return redirect()->route('leave.index')->with('success', 'Leave request status updated successfully.');
    }

    /**
     * Remove the specified leave request.
     */
    public function destroy(LeaveRequest $leave) 
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager', 'admin']);

        if (!$isAdmin) {
            
             if (request()->wantsJson()) {
                 return response()->json(['message' => 'You do not have permission to perform this action.'], 403);
             }
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }

        
        $leave->delete();

        
         if (request()->wantsJson()) {
             return response()->json(['message' => 'Leave request deleted successfully.']);
         }
        return redirect()->route('leave.index')->with('success', 'Leave request deleted successfully.');
    }

    /**
     * Helper function to calculate leave days excluding weekends.
     */
    private function calculateLeaveDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        return $start->diffInDaysFiltered(function(Carbon $date) {
            return !$date->isWeekend(); 
        }, $end) + 1; 
    }
}

