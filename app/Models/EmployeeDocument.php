<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'document_type',
        'title',
        'file_path',
        'description',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    /**
     * Get the employee that owns the document.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope a query to only include documents that are about to expire.
     */
    public function scopeAboutToExpire($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now())
            ->whereDate('expiry_date', '<=', now()->addDays($days));
    }

    /**
     * Scope a query to only include expired documents.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now());
    }
}