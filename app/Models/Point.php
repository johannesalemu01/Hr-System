<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'points',
        'source_type',
        'source_id',
        'description',
        'awarded_by',
    ];

    protected $casts = [
        'source_type' => 'string',
    ];

    /**
     * Get the employee that earned the points.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who awarded the points.
     */
    public function awardedBy()
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    /**
     * Get the source model (polymorphic).
     */
    public function source()
    {
        $sourceMap = [
            'kpi' => KpiRecord::class,
            'badge' => EmployeeBadge::class,
            'attendance' => Attendance::class,
            'bonus' => Bonus::class,
        ];

        if (isset($sourceMap[$this->source_type]) && $this->source_id) {
            $sourceClass = $sourceMap[$this->source_type];
            return $sourceClass::find($this->source_id);
        }

        return null;
    }

    /**
     * Scope a query to only include points earned in a specific date range.
     */
    public function scopeEarnedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include points from a specific source.
     */
    public function scopeFromSource($query, $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }
}