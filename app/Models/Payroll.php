<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_reference',
        'start_date',
        'end_date',
        'payment_date',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Get the payroll items for this payroll.
     */
    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    /**
     * Get the user who created this payroll.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this payroll.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the total gross salary for this payroll.
     */
    public function getTotalGrossSalaryAttribute()
    {
        return $this->payrollItems()->sum('gross_salary');
    }

    /**
     * Get the total net salary for this payroll.
     */
    public function getTotalNetSalaryAttribute()
    {
        return $this->payrollItems()->sum('net_salary');
    }

    /**
     * Get the total deductions for this payroll.
     */
    public function getTotalDeductionsAttribute()
    {
        return $this->payrollItems()->sum('total_deductions');
    }

    /**
     * Get the total bonuses for this payroll.
     */
    public function getTotalBonusesAttribute()
    {
        return $this->payrollItems()->sum('total_bonuses');
    }

    /**
     * Scope a query to only include payrolls with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include payrolls for a specific period.
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->where('start_date', '>=', $startDate)
            ->where('end_date', '<=', $endDate);
    }
}