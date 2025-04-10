<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LeaveRequestController extends Controller
{
    /**
     * Display the leave management page.
     */
    public function index(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user has admin privileges
        $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager']);
        
        // Get all leave types
        $leaveTypes = LeaveType::where('is_active', true)->get();
        
        // Get all departments for filtering
        $departments = Department::orderBy('name')->get();
        
        // Get query parameters for filtering
        $status = $request->query('status');
        $departmentId = $request->query('department_id');
        $leaveTypeId = $request->query('leave_type_id');
        $search = $request->query('search');
        
        // Base query for leave requests
        $query = LeaveRequest::with(['employee', 'leaveType', 'employee.department']);
        
        // If not admin, only show the user's own leave requests
        if (!$isAdmin) {
            $employee = Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                return redirect()->back()->with('error', 'Employee record not found.');
            }
            
            $query->where('employee_id', $employee->id);
        }
        
        // Apply filters
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
        
        // Get paginated results
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
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'total_days' => $request->total_days,
                    'reason' => $request->reason,
                    'status' => $request->status,
                    'rejection_reason' => $request->rejection_reason,
                    'created_at' => $request->created_at,
                ];
            });
        
        // Get employee list for admin to select when creating leave requests
        $employees = [];
        if ($isAdmin) {
            $employees = Employee::orderBy('first_name')
                ->get()
                ->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'employee_id' => $employee->employee_id,
                        'department' => $employee->department ? $employee->department->name : 'N/A',
                    ];
                });
        }
        
        return Inertia::render('Leave/index', [
            'leaveTypes' => $leaveTypes,
            'leaveRequests' => $leaveRequests,
            'departments' => $departments,
            'employees' => $employees,
            'isAdmin' => $isAdmin,
            'filters' => [
                'status' => $status,
                'department_id' => $departmentId,
                'leave_type_id' => $leaveTypeId,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Store a newly created leave request.
     */
    public function store(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user has admin privileges
        $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager']);
        
        // Validate the request
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'employee_id' => $isAdmin ? 'required|exists:employees,id' : 'sometimes',
        ]);
        
        // Determine employee_id
        $employeeId = null;
        
        if ($isAdmin && isset($validated['employee_id'])) {
            // Admin can create leave requests for any employee
            $employeeId = $validated['employee_id'];
        } else {
            // Regular user can only create leave requests for themselves
            $employee = Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                return redirect()->back()->with('error', 'Employee record not found.');
            }
            
            $employeeId = $employee->id;
        }
        
        // Calculate total days (excluding weekends)
        $startDate = new \DateTime($validated['start_date']);
        $endDate = new \DateTime($validated['end_date']);
        $interval = $startDate->diff($endDate);
        $totalDays = $interval->days + 1; // Include both start and end dates
        
        // Adjust for weekends
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dayOfWeek = $currentDate->format('N');
            if ($dayOfWeek >= 6) { // 6 = Saturday, 7 = Sunday
                $totalDays--;
            }
            $currentDate->modify('+1 day');
        }
        
        // Create the leave request
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
     * Update the status of a leave request.
     */
    public function updateStatus(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500',
        ]);
        
        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user has admin privileges
        $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager']);
        
        if (!$isAdmin) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }
        
        // Find the leave request
        $leaveRequest = LeaveRequest::findOrFail($id);
        
        // Update the status
        $leaveRequest->status = $validated['status'];
        $leaveRequest->rejection_reason = $validated['status'] === 'rejected' ? $validated['rejection_reason'] : null;
        $leaveRequest->approved_by = $user->id;
        $leaveRequest->approved_at = now();
        $leaveRequest->save();
        
        return redirect()->route('leave.index')->with('success', 'Leave request status updated successfully.');
    }
}

