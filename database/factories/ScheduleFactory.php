<?php

namespace Database\Factories;

use App\Enums\ScheduleStatus;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $periodStart = now()->startOfMonth();

        return [
            'name' => 'ТЦ '.fake('ru_RU')->company(),
            'address' => fake('ru_RU')->streetAddress(),
            'status' => ScheduleStatus::Draft,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodStart->copy()->endOfMonth()->toDateString(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => ScheduleStatus::Published,
            'published_at' => now(),
        ]);
    }
}
