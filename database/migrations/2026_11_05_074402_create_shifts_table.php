<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_template_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->unique(['schedule_id', 'employee_id', 'date']);
            $table->index(['schedule_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
