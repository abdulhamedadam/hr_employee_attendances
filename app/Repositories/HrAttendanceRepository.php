<?php

namespace App\Repositories;

use App\Models\HrAttendance;
use App\Repositories\Contracts\HrAttendanceRepositoryInterface;

class HrAttendanceRepository implements HrAttendanceRepositoryInterface
{
    protected $model;

    public function __construct(HrAttendance $model)
    {
        $this->model = $model;
    }
    //----------------------------------------------------------
    public function all()
    {
        return $this->model->all();
    }
    //----------------------------------------------------------
    public function find($id)
    {
        return $this->model->find($id);
    }
    //----------------------------------------------------------
    public function create(array $data)
    {
        return $this->model->create($data);
    }
    //----------------------------------------------------------
    public function bulkInsert(array $data)
    {
        return $this->model->insert($data);
    }
    //----------------------------------------------------------
    public function getByEmployee($employeeCode, $startDate = null, $endDate = null)
    {
        $query = $this->model->where('employee_code', $employeeCode);

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        return $query->orderBy('date')->orderBy('time')->get();
    }
    //----------------------------------------------------------
    public function getByDateRange($startDate, $endDate)
    {
        return $this->model->whereBetween('date', [$startDate, $endDate])
            ->orderBy('employee_code')
            ->orderBy('date')
            ->orderBy('time')
            ->get();
    }

    public function deleteByDateRange($startDate, $endDate)
    {
        return $this->model->whereBetween('date', [$startDate, $endDate])->delete();
    }
}
