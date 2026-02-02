<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hr_attendances', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code');
            $table->date('date');
            $table->time('time');
            $table->enum('type', ['check_in', 'check_out'])->nullable();
            $table->timestamps();
            $table->index('employee_code');
            $table->index('date');
            $table->index(['employee_code', 'date']);
            $table->index(['employee_code', 'date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_attendances');
    }
};
