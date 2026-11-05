<?php

namespace Database\Seeders;

use App\Enums\ScheduleStatus;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\ShiftTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $periodStart = now()->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $employees = Employee::query()->orderBy('id')->get();
        $templates = ShiftTemplate::query()->orderBy('sort')->get();

        $schedules = [
            ['name' => 'ТЦ Метрополис', 'address' => 'Ленинградское ш., 16А', 'status' => ScheduleStatus::Published, 'fill' => 0.9, 'employees' => 8],
            ['name' => 'ТЦ Авиапарк', 'address' => 'Ходынский б-р, 4', 'status' => ScheduleStatus::Draft, 'fill' => 0.8, 'employees' => 10],
            ['name' => 'ТЦ Европейский', 'address' => 'пл. Киевского Вокзала, 2', 'status' => ScheduleStatus::Draft, 'fill' => 0.45, 'employees' => 12],
        ];

        foreach ($schedules as $index => $data) {
            $schedule = Schedule::query()->updateOrCreate(['name' => $data['name']], [
                'address' => $data['address'],
                'status' => $data['status'],
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'published_at' => $data['status'] === ScheduleStatus::Published ? now() : null,
            ]);

            $scheduleEmployees = $employees->take($data['employees']);
            $schedule->employees()->sync($scheduleEmployees->pluck('id'));
            $schedule->shifts()->delete();

            $day = $periodStart->copy();
            $dayIndex = 0;

            while ($day->lte($periodEnd)) {
                if ($dayIndex / $periodEnd->day < $data['fill']) {
                    $this->seedDay($schedule, $scheduleEmployees, $templates, $day, $index + $dayIndex);
                }

                $day->addDay();
                $dayIndex++;
            }
        }
    }

    private function seedDay(Schedule $schedule, Collection $employees, Collection $templates, Carbon $day, int $seed): void
    {
        foreach ($employees->values() as $position => $employee) {
            if (($position + $seed) % 3 !== 0) {
                continue;
            }

            $template = $templates[intdiv($position + $seed, 3) % $templates->count()];

            $schedule->shifts()->create([
                'employee_id' => $employee->id,
                'shift_template_id' => $template->id,
                'date' => $day->toDateString(),
                'start_time' => $template->start_time,
                'end_time' => $template->end_time,
            ]);
        }
    }
}
