<?php

namespace App\Repositories\Contracts;

interface HrEmployeeRepositoryInterface
{
    public function all();

    public function find($id);

    public function findByCode($code);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function getEmployeesWithAttendance($startDate, $endDate);
}
