<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth; 
use App\Models\Employee; 
use App\Models\LeaveRequest; 
use Tightenco\Ziggy\Ziggy; 

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = Auth::user();
        $pendingLeaveRequestsCount = 0;

        if ($user) {
            $isAdmin = $user->hasRole(['super-admin', 'hr-admin', 'manager', 'admin']);
            if ($isAdmin) {
                // Admin sees all pending requests
                $pendingLeaveRequestsCount = LeaveRequest::where('status', 'pending')->count();
            } else {
                // Non-admin user: check if associated with an employee
                $employee = Employee::where('user_id', $user->id)->first();
                if ($employee) {
                    // Employee sees only their pending requests
                    $pendingLeaveRequestsCount = LeaveRequest::where('employee_id', $employee->id)
                                                             ->where('status', 'pending')
                                                             ->count();
                }
                // If user is not admin and not linked to an employee, count remains 0
            }
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'profile_picture' => $request->user()->profile_picture, 
                    'roles' => $request->user()->roles->pluck('name'),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            'employee' => fn () => $request->user() && $request->user()->employee
                ? $request->user()->employee->only('id', 'profile_picture', 'first_name', 'last_name', 'department_id', 'position_id')
                : null, 
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'pendingLeaveRequestsCount' => $pendingLeaveRequestsCount, 
        ]);
    }
}
