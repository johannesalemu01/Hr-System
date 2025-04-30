<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\KpiRecord;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'gender',
        'marital_status',
        'address',
        'city',
        'state',
        'country',
        'phone_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'department_id',
        'position_id',
        'manager_id',
        'hire_date',
        'termination_date',
        'employment_status',
        'bank_name',
        'bank_account_number',
        'tax_id',
        'social_security_number',
        'profile_picture',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'gender' => 'string',
        'marital_status' => 'string',
        'employment_status' => 'string',
    ];

    /**
     * Get the user associated with the employee.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department that the employee belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the position of the employee.
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the manager of the employee.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the subordinates of the employee.
     */
    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id', 'user_id');
    }

    /**
     * Get the documents of the employee.
     */
    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /**
     * Get the KPIs assigned to the employee.
     */
    public function kpis()
    {
        return $this->hasMany(EmployeeKpi::class);
    }

    /**
     * Get the KPI records associated with the employee.
     */
    public function kpiRecords()
    {
        return $this->hasManyThrough(
            KpiRecord::class,
            EmployeeKpi::class,
            'employee_id', 
            'employee_kpi_id', 
            'id', 
            'id' 
        );
    }

    /**
     * Get the badges earned by the employee.
     */
    public function badges()
    {
        return $this->hasMany(EmployeeBadge::class);
    }

    /**
     * Get the points earned by the employee.
     */
    public function points()
    {
        return $this->hasMany(Point::class);
    }

    /**
     * Get the salary structures of the employee.
     */
    public function salaryStructures()
    {
        return $this->hasMany(SalaryStructure::class);
    }

    /**
     * Get the current salary structure of the employee.
     */
    public function currentSalaryStructure()
    {
        return $this->hasOne(SalaryStructure::class)->where('is_current', true);
    }

    /**
     * Get the payroll items of the employee.
     */
    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    /**
     * Get the leave requests of the employee.
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the attendance records of the employee.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the full name of the employee.
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name;
    }

    /**
     * Get the age of the employee.
     */
    public function getAgeAttribute()
    {
        return $this->date_of_birth->age;
    }

    /**
     * Get the total points of the employee.
     */
    public function getTotalPointsAttribute()
    {
        return $this->points()->sum('points');
    }

    /**
     * Scope a query to only include active employees.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('termination_date')
            ->where('employment_status', '!=', 'terminated')
            ->where('employment_status', '!=', 'retired');
    }
}
