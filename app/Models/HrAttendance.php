<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrAttendance extends Model
{
    protected $table = 'hr_attendances';
    protected $guarded = [];
    //----------------------------------------------------------
    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    //----------------------------------------------------------
    public function scopeByEmployee($query, $code)
    {
        return $query->where('employee_code', $code);
    }
    //----------------------------------------------------------
    public function scopeByDate($query, $date)
    {
        return $query->where('date', $date);
    }
    //----------------------------------------------------------
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
