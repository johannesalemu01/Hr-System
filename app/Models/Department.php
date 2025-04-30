<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\KpiRecord;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'manager_id', 
    ];

    /**
     * Get the employees belonging to the department.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the user who manages the department (based on the incorrect manager_id storing user_id).
     */
    public function managerUser()
    {
        
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the employee record of the manager through the user relationship.
     * This is an accessor to simplify getting manager details despite the schema issue.
     *
     * Access via $department->manager_employee
     */
    public function getManagerEmployeeAttribute()
    {
        
        $this->loadMissing('managerUser.employee');
        
        return $this->managerUser?->employee;
    }

    /**
     * Get the positions in this department.
     */
    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Get the KPIs associated with this department.
     */
    public function kpis()
    {
        return $this->hasMany(Kpi::class);
    }

    /**
     * Get the leaderboards for this department.
     */
    public function leaderboards()
    {
        return $this->hasMany(Leaderboard::class);
    }

    /**
     * Get the KPI records associated with the department.
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
        )->whereHas('employee', function ($query) {
            $query->whereColumn('employees.department_id', 'departments.id');
        });
    }

    
    
    
    
    
    
}
