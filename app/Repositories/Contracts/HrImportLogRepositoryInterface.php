<?php

namespace App\Repositories\Contracts;

interface HrImportLogRepositoryInterface
{
    public function all();

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function getRecent($limit = 10);

    public function getByStatus($status);
}
