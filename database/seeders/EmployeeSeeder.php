<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            ['Алина', 'Кузнецова', 47],
            ['Тимур', 'Волков', 12],
            ['Виктория', 'Морозова', 45],
            ['Артём', 'Соколов', 13],
            ['Полина', 'Лебедева', 44],
            ['Кирилл', 'Новиков', 53],
            ['Дарья', 'Ерохина', 49],
            ['Егор', 'Гаврилов', 59],
            ['Олег', 'Сергеев', null],
            ['Мария', 'Орлова', 32],
            ['Илья', 'Белов', 60],
            ['Ксения', 'Миронова', null],
        ];

        foreach ($employees as [$firstName, $lastName, $avatarId]) {
            Employee::query()->updateOrCreate(
                ['first_name' => $firstName, 'last_name' => $lastName],
                [
                    'position' => 'агент',
                    'avatar' => $avatarId ? "https://i.pravatar.cc/96?img={$avatarId}" : null,
                ],
            );
        }
    }
}
