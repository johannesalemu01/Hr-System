<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'hours_worked',
        'status',
        'notes',
        'ip_address',
        'location',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'hours_worked' => 'decimal:2',
        'status' => 'string',
    ];

    /**
     * Get the employee that this attendance record belongs to.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate hours worked before saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($attendance) {
            if (is_null($attendance->hours_worked) && $attendance->clock_in && $attendance->clock_out) {
                $clockIn = new \DateTime($attendance->clock_in);
                $clockOut = new \DateTime($attendance->clock_out);
                $interval = $clockIn->diff($clockOut);
                $hours = $interval->h + ($interval->i / 60);
                $attendance->hours_worked = round($hours, 2);
            }
        });
    }

    /**
     * Scope a query to only include attendance records with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include attendance records for a specific date range.
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include present attendance records.
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope a query to only include absent attendance records.
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Scope a query to only include late attendance records.
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }
}