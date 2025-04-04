<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_item_id',
        'deduction_type',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deduction_type' => 'string',
    ];

    /**
     * Get the payroll item that this deduction belongs to.
     */ 
    

    /**
     * Get the payroll item that this deduction belongs to.
     */
    public function payrollItem()
    {
        return $this->belongsTo(PayrollItem::class);
    }

    /**
     * Scope a query to only include deductions of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('deduction_type', $type);
    }
}