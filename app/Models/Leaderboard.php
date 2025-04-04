<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'period_type',
        'start_date',
        'end_date',
        'department_id',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'period_type' => 'string',
    ];

    /**
     * Get the department that this leaderboard belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the top performers for this leaderboard.
     */
    public function getTopPerformersAttribute($limit = 10)
    {
        $query = Employee::query();
        
        if ($this->department_id) {
            $query->where('department_id', $this->department_id);
        }
        
        return $query->withCount(['points as total_points' => function ($query) {
                $query->whereBetween('created_at', [$this->start_date, $this->end_date]);
            }])
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get();
    }

    /**
     * Scope a query to only include active leaderboards.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include current leaderboards.
     */
    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }
}