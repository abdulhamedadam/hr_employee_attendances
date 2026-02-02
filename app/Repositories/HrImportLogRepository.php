<?php

namespace App\Repositories;

use App\Models\HrImportLog;
use App\Repositories\Contracts\HrImportLogRepositoryInterface;

class HrImportLogRepository implements HrImportLogRepositoryInterface
{
    protected $model;

    public function __construct(HrImportLog $model)
    {
        $this->model = $model;
    }
    //----------------------------------------------------------
    public function all()
    {
        return $this->model->all();
    }
    //----------------------------------------------------------
    public function find($id)
    {
        return $this->model->find($id);
    }
    //----------------------------------------------------------
    public function create(array $data)
    {
        return $this->model->create($data);
    }
    //----------------------------------------------------------
    public function update($id, array $data)
    {
        $log = $this->find($id);
        if ($log) {
            $log->update($data);
            return $log;
        }
        return null;
    }
    //----------------------------------------------------------
    public function getRecent($limit = 10)
    {
        return $this->model->orderBy('created_at', 'desc')->limit($limit)->get();
    }
    //----------------------------------------------------------
    public function getByStatus($status)
    {
        return $this->model->where('status', $status)->orderBy('created_at', 'desc')->get();
    }
}
