<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_kpi_id',
        'actual_value',
        'achievement_percentage',
        'record_date',
        'comments',
        'recorded_by',
        'points_earned',
    ];

    protected $casts = [
        'record_date' => 'date',
        'actual_value' => 'decimal:2',
        'achievement_percentage' => 'decimal:2',
    ];

    /**
     * Get the employee KPI that this record belongs to.
     */
    public function employeeKpi()
    {
        return $this->belongsTo(EmployeeKpi::class);
    }

    /**
     * Get the user who recorded this KPI.
     */
    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the employee associated with this KPI record.
     */
    public function employee()
    {
        return $this->hasOneThrough(
            Employee::class,
            EmployeeKpi::class,
            'id', 
            'id', 
            'employee_kpi_id', 
            'employee_id' 
        );
    }

    /**
     * Calculate achievement percentage before saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($record) {
            if (is_null($record->achievement_percentage)) {
                $employeeKpi = $record->employeeKpi;
                
                if ($employeeKpi) {
                    $targetValue = $employeeKpi->target_value;
                    
                    if ($targetValue > 0) {
                        $record->achievement_percentage = min(100, ($record->actual_value / $targetValue) * 100);
                    }
                }
            }
        });
    }
}
