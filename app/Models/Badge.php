<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'points_required',
        'badge_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'badge_type' => 'string',
    ];

    /**
     * Get the employee badges for this badge.
     */
    public function employeeBadges()
    {
        return $this->hasMany(EmployeeBadge::class);
    }

    /**
     * Get the employees who have earned this badge.
     */
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_badges')
            ->withPivot('awarded_date', 'achievement_details', 'awarded_by')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active badges.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include badges of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('badge_type', $type);
    }
}