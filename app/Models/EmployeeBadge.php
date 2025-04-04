<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'badge_id',
        'awarded_date',
        'achievement_details',
        'awarded_by',
    ];

    protected $casts = [
        'awarded_date' => 'date',
    ];

    /**
     * Get the employee that earned the badge.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the badge that was earned.
     */
    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Get the user who awarded the badge.
     */
    public function awardedBy()
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    /**
     * Scope a query to only include badges awarded in a specific date range.
     */
    public function scopeAwardedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('awarded_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include recently awarded badges.
     */
    public function scopeRecentlyAwarded($query, $days = 30)
    {
        return $query->where('awarded_date', '>=', now()->subDays($days));
    }
}