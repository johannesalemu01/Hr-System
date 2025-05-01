<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the employee profile associated with the user.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get the departments managed by this user.
     */
    public function managedDepartments()
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    /**
     * Get the employees managed by this user.
     */
    public function managedEmployees()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    /**
     * Get the KPIs assigned by this user.
     */
    public function assignedKpis()
    {
        return $this->hasMany(EmployeeKpi::class, 'assigned_by');
    }

    /**
     * Get the KPI records recorded by this user.
     */
    public function recordedKpis()
    {
        return $this->hasMany(KpiRecord::class, 'recorded_by');
    }

    /**
     * Get the badges awarded by this user.
     */
    public function awardedBadges()
    {
        return $this->hasMany(EmployeeBadge::class, 'awarded_by');
    }

    /**
     * Get the points awarded by this user.
     */
    public function awardedPoints()
    {
        return $this->hasMany(Point::class, 'awarded_by');
    }

    /**
     * Get the salary structures created by this user.
     */
    public function createdSalaryStructures()
    {
        return $this->hasMany(SalaryStructure::class, 'created_by');
    }

    /**
     * Get the payrolls created by this user.
     */
    public function createdPayrolls()
    {
        return $this->hasMany(Payroll::class, 'created_by');
    }

    /**
     * Get the payrolls approved by this user.
     */
    public function approvedPayrolls()
    {
        return $this->hasMany(Payroll::class, 'approved_by');
    }

    /**
     * Get the leave requests approved by this user.
     */
    public function approvedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    /**
     * Check if the user is an HR admin.
     */
    public function isHrAdmin()
    {
        return $this->hasRole('hr-admin');
    }

    /**
     * Check if the user is a manager.
     */
    public function isManager()
    {
        return $this->hasRole('manager');
    }

    /**
     * Check if the user is an employee.
     */
    public function isEmployee()
    {
        return $this->hasRole('employee');
    }
}
