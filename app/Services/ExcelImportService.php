<?php

namespace App\Services;

use App\Repositories\Contracts\HrImportLogRepositoryInterface;
use App\Jobs\ProcessAttendanceImport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportService
{
    protected $importLogRepository;
    protected $attendanceService;

    public function __construct(
        HrImportLogRepositoryInterface $importLogRepository,
        HrAttendanceService $attendanceService
    ) {
        $this->importLogRepository = $importLogRepository;
        $this->attendanceService = $attendanceService;
    }

    //----------------------------------------------------------
    public function handleUpload($file)
    {
        
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('imports', $filename, 'public');

       
        $totalRows = $this->getRowCount(storage_path('app/public/' . $path));

     
        $importLog = $this->importLogRepository->create([
            'filename' => $filename,
            'total_rows' => $totalRows,
            'status' => 'pending',
        ]);

      
        ProcessAttendanceImport::dispatch($importLog->id, $path);

        return $importLog;
    }

    //----------------------------------------------------------
    protected function getRowCount($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

          
            return max(0, $highestRow - 1);
        } catch (\Exception $e) {
            return 0;
        }
    }

    //----------------------------------------------------------
    public function processExcelFile($importLogId, $filePath)
    {
        $importLog = $this->importLogRepository->find($importLogId);

        if (!$importLog) {
            return;
        }

        try {
            $importLog->markAsProcessing();

            $fullPath = storage_path('app/public/' . $filePath);
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();

            $rows = $worksheet->toArray();

            
            array_shift($rows);

            $processedCount = 0;
            $successCount = 0;
            $failedCount = 0;
            $batchSize = 500;
            $batch = [];

            foreach ($rows as $index => $row) {
                try {
                   
                    if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                        $failedCount++;
                        continue;
                    }

                    $employeeCode = trim($row[0]);
                    $date = $this->parseDate($row[1]);
                    $time = $this->parseTime($row[2]);
                    $notes = isset($row[3]) ? trim($row[3]) : null;

                    if (!$date || !$time || !$employeeCode) {
                        $failedCount++;
                        continue;
                    }

                    $batch[] = [
                        'employee_code' => $employeeCode,
                        'date' => $date,
                        'time' => $time,
                        'type' => null,
                        'notes' => $notes,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $successCount++;

                   
                    if (count($batch) >= $batchSize) {
                        $this->attendanceService->bulkInsertAttendances($batch);
                        $batch = [];
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                }

                $processedCount++;

               
                if ($processedCount % 100 == 0) {
                    $importLog->updateProgress($processedCount, $successCount, $failedCount);
                }
            }

           
            if (count($batch) > 0) {
                $this->attendanceService->bulkInsertAttendances($batch);
            }

           
            $importLog->updateProgress($processedCount, $successCount, $failedCount);
            $importLog->markAsCompleted();
        } catch (\Exception $e) {
            $importLog->markAsFailed($e->getMessage());
        }
    }

   //----------------------------------------------------------
    protected function parseDate($value)
    {
        try {
            if (is_numeric($value)) {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $date->format('Y-m-d');
            }
            $date = \Carbon\Carbon::parse($value);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    //----------------------------------------------------------
    protected function parseTime($value)
    {
        try {
            if (is_numeric($value)) {
                $time = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $time->format('H:i:s');
            }
            $time = \Carbon\Carbon::parse($value);
            return $time->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

   //----------------------------------------------------------
    public function getImportStatus($importLogId)
    {
        return $this->importLogRepository->find($importLogId);
    }

   //----------------------------------------------------------
    public function getRecentImports($limit = 10)
    {
        return $this->importLogRepository->getRecent($limit);
    }
}
