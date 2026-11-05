<?php

namespace Database\Seeders;

use App\Models\ShiftTemplate;
use Illuminate\Database\Seeder;

class ShiftTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['name' => 'Утро', 'start_time' => '07:00', 'end_time' => '15:00', 'color' => 'blue'],
            ['name' => 'День', 'start_time' => '10:00', 'end_time' => '18:00', 'color' => 'teal'],
            ['name' => 'Вечер', 'start_time' => '15:00', 'end_time' => '23:00', 'color' => 'purple'],
        ];

        foreach ($templates as $sort => $template) {
            ShiftTemplate::query()->updateOrCreate(['name' => $template['name']], [...$template, 'sort' => $sort]);
        }
    }
}
