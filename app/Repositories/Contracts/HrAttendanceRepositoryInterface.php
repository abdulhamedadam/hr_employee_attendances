<?php

namespace App\Repositories\Contracts;

interface HrAttendanceRepositoryInterface
{
    public function all();

    public function find($id);

    public function create(array $data);

    public function bulkInsert(array $data);

    public function getByEmployee($employeeCode, $startDate = null, $endDate = null);

    public function getByDateRange($startDate, $endDate);

    public function deleteByDateRange($startDate, $endDate);
}
