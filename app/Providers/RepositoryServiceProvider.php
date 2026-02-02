<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\HrEmployeeRepositoryInterface;
use App\Repositories\Contracts\HrAttendanceRepositoryInterface;
use App\Repositories\Contracts\HrImportLogRepositoryInterface;
use App\Repositories\HrEmployeeRepository;
use App\Repositories\HrAttendanceRepository;
use App\Repositories\HrImportLogRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(HrEmployeeRepositoryInterface::class, HrEmployeeRepository::class);
        $this->app->bind(HrAttendanceRepositoryInterface::class, HrAttendanceRepository::class);
        $this->app->bind(HrImportLogRepositoryInterface::class, HrImportLogRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
