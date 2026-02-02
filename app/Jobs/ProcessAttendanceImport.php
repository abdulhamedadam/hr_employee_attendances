<?php

namespace App\Jobs;

use App\Services\ExcelImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAttendanceImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $importLogId;
    public $filePath;
    //----------------------------------------------------------
    public $tries = 3;

    //----------------------------------------------------------
    public $timeout = 600; // 10 minutes for large files

    //----------------------------------------------------------
    public function __construct($importLogId, $filePath)
    {
        $this->importLogId = $importLogId;
        $this->filePath = $filePath;
    }
    //----------------------------------------------------------
    public function handle(ExcelImportService $excelImportService)
    {
        $excelImportService->processExcelFile($this->importLogId, $this->filePath);
    }

    //----------------------------------------------------------
    public function failed(\Throwable $exception)
    {
        // Log the failure or notify administrators
        Log::error('Import job failed: ' . $exception->getMessage(), [
            'import_log_id' => $this->importLogId,
            'file_path' => $this->filePath,
        ]);
    }
}
