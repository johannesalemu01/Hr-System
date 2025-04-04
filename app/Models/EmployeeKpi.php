<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeKpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'kpi_id',
        'target_value',
        'minimum_value',
        'maximum_value',
        'weight',
        'start_date',
        'end_date',
        'status',
        'notes',
        'assigned_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'target_value' => 'decimal:2',
        'minimum_value' => 'decimal:2',
        'maximum_value' => 'decimal:2',
        'weight' => 'decimal:2',
        'status' => 'string',
    ];

    /**
     * Get the employee that this KPI is assigned to.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the KPI definition.
     */
    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    /**
     * Get the user who assigned this KPI.
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the KPI records for this employee KPI.
     */
    public function records()
    {
        return $this->hasMany(KpiRecord::class);
    }

    /**
     * Get the latest KPI record.
     */
    public function latestRecord()
    {
        return $this->hasOne(KpiRecord::class)->latest();
    }

    /**
     * Calculate the current achievement percentage.
     */
    public function getCurrentAchievementAttribute()
    {
        $latestRecord = $this->latestRecord;
        
        if (!$latestRecord) {
            return 0;
        }
        
        return $latestRecord->achievement_percentage;
    }

    /**
     * Scope a query to only include active KPIs.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include KPIs for the current period.
     */
    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Scope a query to only include completed KPIs.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}