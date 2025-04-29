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
        'manager_id', // This column incorrectly stores user_id due to schema issue
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
        // Relationship based on the current schema where manager_id = users.id
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
        // Load the managerUser relationship if not already loaded
        $this->loadMissing('managerUser.employee');
        // Return the employee associated with the manager user
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
            'employee_id', // Foreign key on EmployeeKpi table
            'employee_kpi_id', // Foreign key on KpiRecord table
            'id', // Local key on Department table
            'id' // Local key on Employee table
        )->whereHas('employee', function ($query) {
            $query->whereColumn('employees.department_id', 'departments.id');
        });
    }

    // IMPORTANT: Remove or comment out the old 'manager' relationship if it existed
    // public function manager()
    // {
    //     // This relationship is incorrect based on the schema issue
    //     // return $this->belongsTo(Employee::class, 'manager_id');
    // }
}
