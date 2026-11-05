<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_schedule', function (Blueprint $table) {
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->primary(['schedule_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_schedule');
    }
};
