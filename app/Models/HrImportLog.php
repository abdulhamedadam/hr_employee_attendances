<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrImportLog extends Model
{
    protected $table = 'hr_import_logs';
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    //----------------------------------------------------------
    public function updateProgress($processed, $success, $failed)
    {
        $this->update([
            'processed_rows' => $processed,
            'success_rows' => $success,
            'failed_rows' => $failed,
        ]);
    }
    //----------------------------------------------------------
    public function markAsProcessing()
    {
        $this->update(['status' => 'processing']);
    }
    //----------------------------------------------------------
    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }
    //----------------------------------------------------------
    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
