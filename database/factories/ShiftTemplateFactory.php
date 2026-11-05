<?php

namespace Database\Factories;

use App\Models\ShiftTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftTemplateFactory extends Factory
{
    protected $model = ShiftTemplate::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Утро', 'День', 'Вечер']),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'color' => fake()->randomElement(['blue', 'teal', 'purple']),
            'sort' => 0,
        ];
    }
}
