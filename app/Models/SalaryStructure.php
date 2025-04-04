<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'basic_salary',
        'housing_allowance',
        'transport_allowance',
        'meal_allowance',
        'medical_allowance',
        'other_allowances',
        'other_allowances_description',
        'effective_date',
        'end_date',
        'is_current',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'basic_salary' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'meal_allowance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
    ];

    /**
     * Get the employee that this salary structure belongs to.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who created this salary structure.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the total salary.
     */
    public function getTotalSalaryAttribute()
    {
        return $this->basic_salary +
            $this->housing_allowance +
            $this->transport_allowance +
            $this->meal_allowance +
            $this->medical_allowance +
            $this->other_allowances;
    }

    /**
     * Scope a query to only include current salary structures.
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope a query to only include salary structures effective at a given date.
     */
    public function scopeEffectiveAt($query, $date)
    {
        return $query->where('effective_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            });
    }
}