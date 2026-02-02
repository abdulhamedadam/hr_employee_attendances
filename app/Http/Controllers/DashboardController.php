<?php

namespace App\Http\Controllers;

use App\Services\HrAttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $attendanceService;

    public function __construct(HrAttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    //----------------------------------------------------------
    public function getStats(Request $request)
    {
        try {
            $monthStr = $request->input('month', now()->format('Y-m'));
            $carbonMonth = Carbon::parse($monthStr . '-01');
            $startDate = $carbonMonth->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $carbonMonth->copy()->endOfMonth()->format('Y-m-d');

            $attendances = $this->attendanceService->getAttendanceByDateRange($startDate, $endDate);

            $standardHours = 8;
            $dailyActualHours = array_fill(1, $carbonMonth->daysInMonth, 0);
            $dailyEmpCount = array_fill(1, $carbonMonth->daysInMonth, 0);

            $employeeStats = [];
            $groupedByEmpDate = [];

            foreach ($attendances as $row) {
                $empCode = $row->employee_code;
                $date = $row->date instanceof Carbon ? $row->date->format('Y-m-d') : Carbon::parse($row->date)->format('Y-m-d');
                $key = $empCode . '_' . $date;

                if (!isset($groupedByEmpDate[$key])) {
                    $groupedByEmpDate[$key] = ['in' => $row->time, 'out' => $row->time, 'date' => $date];
                } else {
                    if ($row->time < $groupedByEmpDate[$key]['in']) $groupedByEmpDate[$key]['in'] = $row->time;
                    if ($row->time > $groupedByEmpDate[$key]['out']) $groupedByEmpDate[$key]['out'] = $row->time;
                }
            }

            foreach ($groupedByEmpDate as $key => $data) {
                $parts = explode('_', $key);
                $empCode = $parts[0];
                $day = Carbon::parse($data['date'])->day;

                if (!isset($employeeStats[$empCode])) {
                    $employeeStats[$empCode] = [
                        'code' => $empCode,
                        'name' => 'موظف (' . $empCode . ')',
                        'late_minutes' => 0,
                        'work_days' => 0,
                        'total_work_minutes' => 0
                    ];
                }

                $employeeStats[$empCode]['work_days']++;

                // Late calculation
                if ($data['in'] > '08:00:00') {
                    $employeeStats[$empCode]['late_minutes'] += Carbon::parse('08:00:00')->diffInMinutes(Carbon::parse($data['in']));
                }

                // Work duration
                if ($data['in'] < $data['out']) {
                    $minutes = Carbon::parse($data['in'])->diffInMinutes(Carbon::parse($data['out']));
                    $employeeStats[$empCode]['total_work_minutes'] += $minutes;
                    $dailyActualHours[$day] += ($minutes / 60);
                    $dailyEmpCount[$day]++;
                }
            }

            // Calculate chart data
            $chartActualData = [];
            foreach ($dailyActualHours as $day => $totalHours) {
                $chartActualData[] = $dailyEmpCount[$day] > 0 ? round($totalHours / $dailyEmpCount[$day], 1) : 0;
            }

            // Formatting Ranking and Summary
            $ranking = collect($employeeStats)->values()->map(function ($emp) {
                $emp['late_formatted'] = $this->formatMinutes($emp['late_minutes']);
                return $emp;
            });

            $totalLateMinutes = $ranking->sum('late_minutes');

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_punches' => count($attendances),
                    'total_late_formatted' => $this->formatMinutes($totalLateMinutes),
                    'total_employees' => $ranking->count(),
                ],
                'ranking' => [
                    'top' => $ranking->sortBy('late_minutes')->sortByDesc('work_days')->take(5)->values(),
                    'bottom' => $ranking->sortByDesc('late_minutes')->take(5)->values()
                ],
                'charts' => [
                    'labels' => range(1, $carbonMonth->daysInMonth),
                    'actual_data' => $chartActualData,
                    'standard_data' => array_fill(1, $carbonMonth->daysInMonth, $standardHours),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    //----------------------------------------------------------
    public function getEmployeeChartData(Request $request)
    {
        try {
            $empCode = $request->input('employee_code');
            $monthStr = $request->input('month', now()->format('Y-m'));

            if (!$empCode) {
                throw new \Exception('يجب اختيار موظف');
            }

            $carbonMonth = Carbon::parse($monthStr . '-01');
            $startDate = $carbonMonth->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $carbonMonth->copy()->endOfMonth()->format('Y-m-d');

            $attendances = $this->attendanceService->getAttendanceByEmployee($empCode, $startDate, $endDate);

            $dailyMinutes = array_fill(1, $carbonMonth->daysInMonth, 0);
            $groupedByDate = [];

            foreach ($attendances as $row) {
                $date = $row->date instanceof Carbon ? $row->date->format('Y-m-d') : Carbon::parse($row->date)->format('Y-m-d');
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = ['in' => $row->time, 'out' => $row->time];
                } else {
                    if ($row->time < $groupedByDate[$date]['in']) $groupedByDate[$date]['in'] = $row->time;
                    if ($row->time > $groupedByDate[$date]['out']) $groupedByDate[$date]['out'] = $row->time;
                }
            }

            foreach ($groupedByDate as $date => $data) {
                if ($data['in'] < $data['out']) {
                    $day = Carbon::parse($date)->day;
                    $minutes = Carbon::parse($data['in'])->diffInMinutes(Carbon::parse($data['out']));
                    $dailyMinutes[$day] = round($minutes / 60, 1);
                }
            }

            return response()->json([
                'success' => true,
                'labels' => range(1, $carbonMonth->daysInMonth),
                'data' => array_values($dailyMinutes)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    //----------------------------------------------------------
    protected function formatMinutes($totalMinutes)
    {
        $hours = floor($totalMinutes / 60);
        $mins = round($totalMinutes % 60);
        return sprintf('%02d:%02d', $hours, $mins);
    }
}
