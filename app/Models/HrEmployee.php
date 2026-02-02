<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrEmployee extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr_employees';
    protected $guarded = [];
    //----------------------------------------------------------
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    //----------------------------------------------------------
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    //----------------------------------------------------------
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    //----------------------------------------------------------
    public function attendances()
    {
        return $this->hasMany(HrAttendance::class, 'employee_code', 'code');
    }
}
