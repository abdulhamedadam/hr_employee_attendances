<?php

namespace App\Repositories;

use App\Models\HrEmployee;
use App\Repositories\Contracts\HrEmployeeRepositoryInterface;

class HrEmployeeRepository implements HrEmployeeRepositoryInterface
{
    protected $model;
    //----------------------------------------------------------
    public function __construct(HrEmployee $model)
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
    public function findByCode($code)
    {
        return $this->model->where('code', $code)->first();
    }
    //----------------------------------------------------------
    public function create(array $data)
    {
        return $this->model->create($data);
    }
    //----------------------------------------------------------
    public function update($id, array $data)
    {
        $employee = $this->find($id);
        if ($employee) {
            $employee->update($data);
            return $employee;
        }
        return null;
    }
    //----------------------------------------------------------
    public function delete($id)
    {
        $employee = $this->find($id);
        if ($employee) {
            return $employee->delete();
        }
        return false;
    }
    //----------------------------------------------------------
    public function getEmployeesWithAttendance($startDate, $endDate)
    {
        return $this->model->with(['attendances' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }])->get();
    }
}
