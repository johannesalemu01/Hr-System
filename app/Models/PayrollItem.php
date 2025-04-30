<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'basic_salary',
        'total_allowances',
        'total_deductions',
        'total_bonuses',
        'gross_salary',
        'net_salary',
        'working_days',
        'leave_days',
        'absent_days',
        'notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_bonuses' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    /**
     * Get the payroll that this item belongs to.
     */
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    /**
     * Get the employee that this payroll item is for.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the deductions for this payroll item.
     */
    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }

    /**
     * Get the bonuses for this payroll item.
     */
    public function bonuses()
    {
        return $this->hasMany(Bonus::class);
    }

    /**
     * Calculate gross and net salary before saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($payrollItem) {
            
            if (is_null($payrollItem->gross_salary)) {
                $payrollItem->gross_salary = $payrollItem->basic_salary + $payrollItem->total_allowances + $payrollItem->total_bonuses;
            }
            
            
            if (is_null($payrollItem->net_salary)) {
                $payrollItem->net_salary = $payrollItem->gross_salary - $payrollItem->total_deductions;
            }
        });
    }
}