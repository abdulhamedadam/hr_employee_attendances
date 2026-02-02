<?php

namespace App\Http\Controllers;

use App\Services\HrAttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    protected $attendanceService;
    //----------------------------------------------------------
    public function __construct(HrAttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }
    //----------------------------------------------------------
    public function index()
    {
        $employeeCodes = $this->getUniqueEmployeeCodes();
        return view('attendance.report', compact('employeeCodes'));
    }
    //----------------------------------------------------------
    public function monthly()
    {
        $employeeCodes = $this->getUniqueEmployeeCodes();
        return view('attendance.monthly', compact('employeeCodes'));
    }
    //----------------------------------------------------------
    public function dashboard()
    {
        $employeeCodes = $this->getUniqueEmployeeCodes();
        return view('attendance.dashboard', compact('employeeCodes'));
    }
    //----------------------------------------------------------
    protected function getUniqueEmployeeCodes()
    {
        return DB::table('hr_attendances')
            ->select('employee_code')
            ->distinct()
            ->orderBy('employee_code')
            ->pluck('employee_code');
    }
    //----------------------------------------------------------
    public function getData(Request $request)
    {
        try {
            $employeeCode = $request->input('employee_code');
            $date = $request->input('date');
            $month = $request->input('month');

            $startDate = null;
            $endDate = null;

            if ($month) {
                $carbonMonth = Carbon::parse($month . '-01');
                $startDate = $carbonMonth->copy()->startOfMonth()->format('Y-m-d');
                $endDate = $carbonMonth->copy()->endOfMonth()->format('Y-m-d');
            } elseif ($date) {
                $startDate = Carbon::parse($date)->format('Y-m-d');
                $endDate = $startDate;
            } elseif (!$employeeCode) {
                $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
            }

            if ($employeeCode) {
                $attendances = $this->attendanceService->getAttendanceByEmployee($employeeCode, $startDate, $endDate);
            } else {
                $attendances = $this->attendanceService->getAttendanceByDateRange($startDate, $endDate);
            }

            $reportData = $this->processAttendanceData($attendances);

            return response()->json(['success' => true, 'data' => $reportData]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    //----------------------------------------------------------
    public function getMonthlyData(Request $request)
    {
        try {
            $monthStr = $request->input('month', now()->format('Y-m'));
            $carbonMonth = Carbon::parse($monthStr . '-01');
            $startDate = $carbonMonth->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $carbonMonth->copy()->endOfMonth()->format('Y-m-d');
            $daysInMonth = $carbonMonth->daysInMonth;

            $attendances = $this->attendanceService->getAttendanceByDateRange($startDate, $endDate);

            // Re-use logic to group accurately
            $processed = $this->processAttendanceData($attendances);

            $matrix = [];
            foreach ($processed as $row) {
                $code = $row['employee_code'];
                $day = (int) Carbon::parse($row['date'])->day;

                if (!isset($matrix[$code])) {
                    $matrix[$code] = [];
                }

                $matrix[$code][$day] = $row;
            }

            $employeeCodes = $this->getUniqueEmployeeCodes();
            $result = [];
            foreach ($employeeCodes as $code) {
                $employeeDays = [];
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $employeeDays[$d] = $matrix[$code][$d] ?? null;
                }
                $result[] = [
                    'employee_code' => $code,
                    'days' => $employeeDays
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $result,
                'days_in_month' => $daysInMonth,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    //----------------------------------------------------------
    protected function processAttendanceData($attendances)
    {
        $grouped = [];
        $shiftStart = '08:00:00';
        $shiftEnd = '16:00:00';
        $midDay = '12:00:00';

        foreach ($attendances as $attendance) {
            $dateStr = $attendance->date instanceof Carbon ? $attendance->date->format('Y-m-d') : Carbon::parse($attendance->date)->format('Y-m-d');
            $key = $attendance->employee_code . '_' . $dateStr;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'employee_code' => $attendance->employee_code,
                    'employee_name' => 'موظف (' . $attendance->employee_code . ')',
                    'date' => $dateStr,
                    'check_in' => null,
                    'check_out' => null,
                    'total_hours' => null,
                    'late_formatted' => '00:00',
                    'overtime_formatted' => '00:00',
                    'all_records' => [],
                    'raw_punches' => []
                ];
            }
            $grouped[$key]['raw_punches'][] = $attendance->time;
            $grouped[$key]['all_records'][] = [
                'time' => Carbon::parse($attendance->time)->format('H:i'),
                'notes' => $attendance->notes
            ];
        }

        foreach ($grouped as &$record) {
            sort($record['raw_punches']);
            $punches = $record['raw_punches'];
            $foundIn = null;
            $foundOut = null;

            if (count($punches) === 1) {
                if ($punches[0] < $midDay) $foundIn = $punches[0];
                else $foundOut = $punches[0];
            } else {
                foreach ($punches as $p) {
                    if ($p >= $shiftStart) {
                        $foundIn = $p;
                        break;
                    }
                }
                if (!$foundIn) $foundIn = $punches[0];
                foreach ($punches as $p) {
                    if ($p >= $shiftEnd) {
                        $foundOut = $p;
                        break;
                    }
                }
                if (!$foundOut) {
                    $last = end($punches);
                    if ($last !== $foundIn) $foundOut = $last;
                }
            }

            $record['check_in'] = $foundIn ? Carbon::parse($foundIn)->format('H:i') : null;
            $record['check_out'] = $foundOut ? Carbon::parse($foundOut)->format('H:i') : null;

            if ($foundIn && $foundIn > $shiftStart) {
                $diff = Carbon::parse($shiftStart)->diffInMinutes(Carbon::parse($foundIn));
                $record['late_formatted'] = sprintf('%02d:%02d', floor($diff / 60), $diff % 60);
            }
            if ($foundOut && $foundOut > $shiftEnd) {
                $diff = Carbon::parse($shiftEnd)->diffInMinutes(Carbon::parse($foundOut));
                $record['overtime_formatted'] = sprintf('%02d:%02d', floor($diff / 60), $diff % 60);
            }
            if ($foundIn && $foundOut && $foundIn < $foundOut) {
                $diff = Carbon::parse($foundIn)->diff(Carbon::parse($foundOut));
                $record['total_hours'] = sprintf('%02d:%02d', $diff->h, $diff->i);
            } else {
                $record['total_hours'] = '00:00';
            }
            unset($record['raw_punches']);
        }
        return array_values($grouped);
    }
}
