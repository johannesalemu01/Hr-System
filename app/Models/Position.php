<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'department_id',
        'min_salary',
        'max_salary',
    ];

    /**
     * Get the department that this position belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the employees with this position.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the KPIs associated with this position.
     */
    public function kpis()
    {
        return $this->hasMany(Kpi::class);
    }
}