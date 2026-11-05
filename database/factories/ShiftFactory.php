<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            'employee_id' => Employee::factory(),
            'shift_template_id' => null,
            'date' => now()->startOfMonth()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '18:00',
        ];
    }
}
