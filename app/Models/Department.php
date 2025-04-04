<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'manager_id',
    ];

    /**
     * Get the manager of the department.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the positions in this department.
     */
    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Get the employees in this department.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the KPIs associated with this department.
     */
    public function kpis()
    {
        return $this->hasMany(Kpi::class);
    }

    /**
     * Get the leaderboards for this department.
     */
    public function leaderboards()
    {
        return $this->hasMany(Leaderboard::class);
    }
}