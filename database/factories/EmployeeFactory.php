<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'first_name' => fake('ru_RU')->firstName(),
            'last_name' => fake('ru_RU')->lastName(),
            'position' => 'агент',
            'avatar' => null,
        ];
    }
}
