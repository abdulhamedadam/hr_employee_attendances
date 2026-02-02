<?php

namespace App\Services;

use App\Repositories\Contracts\HrEmployeeRepositoryInterface;

class HrEmployeeService
{
    protected $employeeRepository;
  //----------------------------------------------------------
    public function __construct(HrEmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }
  //----------------------------------------------------------
    public function getAllEmployees()
    {
        return $this->employeeRepository->all();
    }
  //----------------------------------------------------------
    public function getEmployeeById($id)
    {
        return $this->employeeRepository->find($id);
    }
  //----------------------------------------------------------
    public function getEmployeeByCode($code)
    {
        return $this->employeeRepository->findByCode($code);
    }
  //----------------------------------------------------------
    public function createEmployee(array $data)
    {
        return $this->employeeRepository->create($data);
    }
  //----------------------------------------------------------
    public function updateEmployee($id, array $data)
    {
        return $this->employeeRepository->update($id, $data);
    }
  //----------------------------------------------------------
    public function deleteEmployee($id)
    {
        return $this->employeeRepository->delete($id);
    }
  //----------------------------------------------------------
    public function getEmployeesWithAttendance($startDate, $endDate)
    {
        return $this->employeeRepository->getEmployeesWithAttendance($startDate, $endDate);
    }
  //----------------------------------------------------------
    public function getOrCreateEmployee($code, $name = null)
    {
        $employee = $this->getEmployeeByCode($code);

        if (!$employee && $name) {
            $employee = $this->createEmployee([
                'code' => $code,
                'name' => $name,
            ]);
        }

        return $employee;
    }
}
