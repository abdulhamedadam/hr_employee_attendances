<?php

namespace App\Http\Controllers;

use App\Services\ExcelImportService;
use Illuminate\Http\Request;

class AttendanceImportController extends Controller
{
    protected $excelImportService;

    public function __construct(ExcelImportService $excelImportService)
    {
        $this->excelImportService = $excelImportService;
    }

    //----------------------------------------------------------
    public function index()
    {
        $recentImports = $this->excelImportService->getRecentImports(10);
        return view('attendance.import', compact('recentImports'));
    }

    //----------------------------------------------------------
    public function upload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $importLog = $this->excelImportService->handleUpload($request->file('excel_file'));

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully. Processing started.',
                'import_id' => $importLog->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    //----------------------------------------------------------
    public function status($importId)
    {
        $importLog = $this->excelImportService->getImportStatus($importId);

        if (!$importLog) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $importLog->id,
                'filename' => $importLog->filename,
                'status' => $importLog->status,
                'total_rows' => $importLog->total_rows,
                'processed_rows' => $importLog->processed_rows,
                'success_rows' => $importLog->success_rows,
                'failed_rows' => $importLog->failed_rows,
                'progress_percentage' => $importLog->total_rows > 0
                    ? round(($importLog->processed_rows / $importLog->total_rows) * 100, 2)
                    : 0,
                'error_message' => $importLog->error_message,
            ],
        ]);
    }
}
