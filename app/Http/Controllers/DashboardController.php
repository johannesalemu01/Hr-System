<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\Department;
use App\Models\KpiRecord;
use App\Models\EmployeeKpi;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Badge;
use App\Models\EmployeeBadge;
use App\Models\Event; 
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 

class DashboardController extends Controller
{
    
    private $adminRoles = ['super-admin', 'admin', 'hr-admin', 'manager'];

    /**
     * Display the dashboard page.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();
        $isEmployeeView = !$user->hasAnyRole($this->adminRoles);
        $isAdminOrManager = $user->hasAnyRole($this->adminRoles); 

        
        $stats = [];
        $kpiPerformanceData = ['labels' => [], 'datasets' => []];
        $departmentDistributionData = ['labels' => [], 'datasets' => []];
        $recentActivities = [];
        $upcomingEvents = [];

        if ($isAdminOrManager) {
            
            $stats = $this->getStats();
            $kpiPerformanceData = $this->getKpiPerformanceData();
            $departmentDistributionData = $this->getDepartmentDistributionData();
            $recentActivities = $this->getRecentActivities();
            $upcomingEvents = $this->getUpcomingEvents();
        } else {
            
            $upcomingEvents = $this->getUpcomingEvents();
        }

        return Inertia::render('Dashboard/index', [
            'stats' => $stats,
            'kpiPerformanceData' => $kpiPerformanceData,
            'departmentDistributionData' => $departmentDistributionData,
            'recentActivities' => $recentActivities,
            'upcomingEvents' => $upcomingEvents,
            'isEmployeeView' => $isEmployeeView, 
            'isAdminOrManager' => $isAdminOrManager, 
        ]);
    }

    /**
     * Get dashboard statistics. (Only called for Admin/Manager)
     *
     * @return array
     */
    private function getStats()
    {
        
        $totalEmployees = Employee::count();
        
        
        $averageKpiScore = KpiRecord::avg('achievement_percentage');
        $averageKpiScore = round($averageKpiScore ?? 0, 1);
        
        
        $pendingLeaveRequests = LeaveRequest::where('status', 'pending')->count();
        
        
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $workingDays = $this->getWorkingDaysCount($startOfMonth, $endOfMonth);
        
        $totalAttendances = Attendance::whereBetween('date', [$startOfMonth, Carbon::now()])
            ->where('status', 'present')
            ->count();
        
        $totalExpectedAttendances = Employee::where(function($query) use ($startOfMonth) {
                $query->where('hire_date', '<=', Carbon::now())
                    ->where(function($q) use ($startOfMonth) {
                        $q->whereNull('termination_date')
                            ->orWhere('termination_date', '>=', $startOfMonth);
                    });
            })
            ->count() * $workingDays;
        
        $attendanceRate = $totalExpectedAttendances > 0 
            ? round(($totalAttendances / $totalExpectedAttendances) * 100, 1) 
            : 0;
        
        
        
        
        
        return [
            'totalEmployees' => $totalEmployees,
            'averageKpiScore' => $averageKpiScore,
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'attendanceRate' => $attendanceRate,
        ];
    }
    
    /**
     * Get KPI performance data for the last 6 months. (Only called for Admin/Manager)
     *
     * @return array
     */
    private function getKpiPerformanceData()
    {
        $months = [];
        $data = [];
        
        
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M');
            
            
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $avgScore = KpiRecord::whereBetween('record_date', [$monthStart, $monthEnd])
                ->avg('achievement_percentage');
            
            $data[] = round($avgScore ?? 0, 1);
        }
        
        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Average KPI Score',
                    'data' => $data,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ]
            ]
        ];
    }
    
    /**
     * Get department distribution data. (Only called for Admin/Manager)
     *
     * @return array
     */
    private function getDepartmentDistributionData()
    {
        $departments = Department::withCount('employees')
            ->orderBy('employees_count', 'desc')
            ->take(5)
            ->get();
        
        $labels = $departments->pluck('name')->toArray();
        $data = $departments->pluck('employees_count')->toArray();
        
        
        $colors = [
            '#3b82f6', 
            '#10b981', 
            '#f59e0b', 
            '#6366f1', 
            '#ec4899', 
        ];
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                ]
            ]
        ];
    }
    
    /**
     * Get recent activities. (Only called for Admin/Manager)
     *
     * @return array
     */
    private function getRecentActivities()
    {
        $activities = [];
        
        
        $kpiRecords = KpiRecord::with(['employeeKpi.employee', 'employeeKpi.kpi'])
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();
        
        foreach ($kpiRecords as $record) {
            $employee = $record->employeeKpi->employee;
            $activities[] = [
                'id' => 'kpi_' . $record->id,
                'user' => [
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'avatar' => $employee->profile_picture ?? '/placeholder.svg?height=40&width=40',
                ],
                'action' => 'completed KPI assessment',
                'target' => $record->employeeKpi->kpi->name,
                'date' => $this->formatTimeAgo($record->created_at),
                'icon' => 'chart-bar',
                'iconColor' => 'bg-green-100 text-green-600',
            ];
        }
        
        
        $leaveRequests = LeaveRequest::with(['employee', 'leaveType'])
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();
        
        foreach ($leaveRequests as $request) {
            $activities[] = [
                'id' => 'leave_' . $request->id,
                'user' => [
                    'name' => $request->employee->first_name . ' ' . $request->employee->last_name,
                    'avatar' => $request->employee->profile_picture ?? '/placeholder.svg?height=40&width=40',
                ],
                'action' => 'requested leave',
                'target' => $request->leaveType->name . ' (' . $request->total_days . ' days)',
                'date' => $this->formatTimeAgo($request->created_at),
                'icon' => 'calendar',
                'iconColor' => 'bg-amber-100 text-amber-600',
            ];
        }
        
        
        $payrolls = Payroll::where('status', 'approved')
            ->orderBy('approved_at', 'desc')
            ->take(1)
            ->get();
        
        foreach ($payrolls as $payroll) {
            $approver = Employee::whereHas('user', function($query) use ($payroll) {
                $query->where('id', $payroll->approved_by);
            })->first();
            
            if ($approver) {
                $activities[] = [
                    'id' => 'payroll_' . $payroll->id,
                    'user' => [
                        'name' => $approver->first_name . ' ' . $approver->last_name,
                        'avatar' => $approver->profile_picture ?? '/placeholder.svg?height=40&width=40',
                    ],
                    'action' => 'approved payroll',
                    'target' => $payroll->payroll_reference,
                    'date' => $this->formatTimeAgo($payroll->approved_at),
                    'icon' => 'currency-dollar',
                    'iconColor' => 'bg-blue-100 text-blue-600',
                ];
            }
        }
        
        
        $badgeAwards = EmployeeBadge::with(['employee', 'badge'])
            ->orderBy('awarded_date', 'desc')
            ->take(1)
            ->get();
        
        foreach ($badgeAwards as $award) {
            $activities[] = [
                'id' => 'badge_' . $award->id,
                'user' => [
                    'name' => $award->employee->first_name . ' ' . $award->employee->last_name,
                    'avatar' => $award->employee->profile_picture ?? '/placeholder.svg?height=40&width=40',
                ],
                'action' => 'earned badge',
                'target' => $award->badge->name,
                'date' => $this->formatTimeAgo($award->awarded_date),
                'icon' => 'badge-check',
                'iconColor' => 'bg-indigo-100 text-indigo-600',
            ];
        }
        
        
        usort($activities, function($a, $b) {
            return $this->parseTimeAgo($b['date']) <=> $this->parseTimeAgo($a['date']);
        });
        
        return array_slice($activities, 0, 4);
    }
    
    /**
     * Get upcoming events. (Called for everyone)
     *
     * @return array
     */
    private function getUpcomingEvents()
    {
        $events = [];

        
        $upcomingEvents = Event::where('event_date', '>=', Carbon::now())
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();

        foreach ($upcomingEvents as $event) {
            $events[] = [
                'id' => 'event_' . $event->id,
                'title' => $event->title,
                'date' => $event->event_date->format('M d, Y'),
                'time' => $event->event_date->format('h:i A'),
                'type' => $event->type,
                'icon' => $this->getEventIcon($event->type),
                'iconColor' => $this->getEventIconColor($event->type),
                'description' => $event->description, 
            ];
        }

        return $events;
    }

    /**
     * Get the icon for a specific event type.
     *
     * @param string $type
     * @return string
     */
    private function getEventIcon($type)
    {
        $icons = [
            'payroll' => 'currency-dollar',
            'meeting' => 'chart-bar',
            'onboarding' => 'user-add',
            'holiday' => 'calendar',
            'birthday' => 'cake',
            'anniversary' => 'clipboard-check',
        ];

        return $icons[$type] ?? 'calendar';
    }

    /**
     * Get the icon color for a specific event type.
     *
     * @param string $type
     * @return string
     */
    private function getEventIconColor($type)
    {
        $colors = [
            'payroll' => 'bg-blue-100 text-blue-600',
            'meeting' => 'bg-green-100 text-green-600',
            'onboarding' => 'bg-indigo-100 text-indigo-600',
            'holiday' => 'bg-amber-100 text-amber-600',
            'birthday' => 'bg-pink-100 text-pink-600',
            'anniversary' => 'bg-yellow-100 text-yellow-600',
        ];

        return $colors[$type] ?? 'bg-gray-100 text-gray-600';
    }
    
    /**
     * Format a timestamp as a human-readable time ago string.
     *
     * @param string|Carbon $timestamp
     * @return string
     */
    private function formatTimeAgo($timestamp)
    {
        if (!($timestamp instanceof Carbon)) {
            $timestamp = Carbon::parse($timestamp);
        }
        
        $now = Carbon::now();
        $diff = $timestamp->diffInSeconds($now);
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 172800) {
            return 'Yesterday';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' days ago';
        } else {
            return $timestamp->format('M d, Y');
        }
    }
    
    /**
     * Parse a time ago string back to a timestamp for sorting.
     *
     * @param string $timeAgo
     * @return int
     */
    private function parseTimeAgo($timeAgo)
    {
        $now = time();
        
        if (strpos($timeAgo, 'just now') !== false) {
            return $now;
        } elseif (preg_match('/(\d+) minute[s]? ago/', $timeAgo, $matches)) {
            return $now - ($matches[1] * 60);
        } elseif (preg_match('/(\d+) hour[s]? ago/', $timeAgo, $matches)) {
            return $now - ($matches[1] * 3600);
        } elseif (strpos($timeAgo, 'Yesterday') !== false) {
            return $now - 86400;
        } elseif (preg_match('/(\d+) days ago/', $timeAgo, $matches)) {
            return $now - ($matches[1] * 86400);
        } else {
            return strtotime($timeAgo);
        }
    }
    
    /**
     * Get the number of working days between two dates.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return int
     */
    private function getWorkingDaysCount($startDate, $endDate)
    {
        $workingDays = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            
            $dayOfWeek = $currentDate->format('N');
            if ($dayOfWeek < 6) {
                $workingDays++;
            }
            
            $currentDate->addDay();
        }
        
        return $workingDays;
    }
}