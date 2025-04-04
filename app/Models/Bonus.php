<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_item_id',
        'bonus_type',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bonus_type' => 'string',
    ];

    /**
     * Get the payroll item that this bonus belongs to.
     */
    public function payrollItem()
    {
        return $this->belongsTo(PayrollItem::class);
    }

    /**
     * Scope a query to only include bonuses of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('bonus_type', $type);
    }
}