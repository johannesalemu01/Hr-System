<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'measurement_unit',
        'frequency',
        'department_id',
        'position_id',
        'is_active',
        'points_value',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'measurement_unit' => 'string',
        'frequency' => 'string',
    ];

    /**
     * Get the department that this KPI belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the position that this KPI belongs to.
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the employee KPIs for this KPI.
     */
    public function employeeKpis()
    {
        return $this->hasMany(EmployeeKpi::class);
    }

    /**
     * Scope a query to only include active KPIs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include KPIs for a specific department.
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope a query to only include KPIs for a specific position.
     */
    public function scopeForPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }
}