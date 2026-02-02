<?php

namespace App\Services;

use App\Repositories\Contracts\HrAttendanceRepositoryInterface;
use Carbon\Carbon;

class HrAttendanceService
{
    protected $attendanceRepository;
  //----------------------------------------------------------
    public function __construct(HrAttendanceRepositoryInterface $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }
  //----------------------------------------------------------
    public function getAllAttendances()
    {
        return $this->attendanceRepository->all();
    }
  //----------------------------------------------------------
    public function getAttendanceById($id)
    {
        return $this->attendanceRepository->find($id);
    }
  //----------------------------------------------------------
    public function createAttendance(array $data)
    {
        return $this->attendanceRepository->create($data);
    }
  //----------------------------------------------------------
    public function bulkInsertAttendances(array $data)
    {
        return $this->attendanceRepository->bulkInsert($data);
    }
  //----------------------------------------------------------
    public function getEmployeeAttendance($employeeCode, $startDate = null, $endDate = null)
    {
        return $this->attendanceRepository->getByEmployee($employeeCode, $startDate, $endDate);
    }
  //----------------------------------------------------------
    public function getAttendanceByEmployee($employeeCode, $startDate = null, $endDate = null)
    {
        return $this->getEmployeeAttendance($employeeCode, $startDate, $endDate);
    }
  //----------------------------------------------------------
    public function getAttendanceByDateRange($startDate, $endDate)
    {
        return $this->attendanceRepository->getByDateRange($startDate, $endDate);
    }
  //----------------------------------------------------------
    public function deleteAttendanceByDateRange($startDate, $endDate)
    {
        return $this->attendanceRepository->deleteByDateRange($startDate, $endDate);
    }
    //----------------------------------------------------------
    public function processAttendanceRecords(array $records)
    {
        $processed = [];
        $grouped = [];

    
        foreach ($records as $record) {
            $key = $record['employee_code'] . '_' . $record['date'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $record;
        }

        
        foreach ($grouped as $key => $group) {
        
            usort($group, function ($a, $b) {
                return strcmp($a['time'], $b['time']);
            });

        
            if (count($group) > 0) {
                $group[0]['type'] = 'check_in';
                $processed[] = $group[0];

                if (count($group) > 1) {
                    $lastIndex = count($group) - 1;
                    $group[$lastIndex]['type'] = 'check_out';
                    $processed[] = $group[$lastIndex];
                }
            }
        }

        return $processed;
    }
}
