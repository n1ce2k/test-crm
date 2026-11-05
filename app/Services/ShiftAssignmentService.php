<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Shift;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShiftAssignmentService
{
    public function assign(Schedule $schedule, int $employeeId, string $date, array $attributes): Shift
    {
        $shift = DB::transaction(fn (): Shift => $schedule->shifts()->updateOrCreate(
            ['employee_id' => $employeeId, 'date' => $date],
            $attributes,
        ));

        $this->markScheduleChanged($schedule);

        return $shift;
    }

    public function assignMany(Schedule $schedule, array $cells, array $attributes): Collection
    {
        $shifts = DB::transaction(fn (): Collection => collect($cells)
            ->unique(fn (array $cell): string => $cell['employee_id'].'|'.$cell['date'])
            ->map(fn (array $cell): Shift => $schedule->shifts()->updateOrCreate(
                ['employee_id' => $cell['employee_id'], 'date' => $cell['date']],
                $attributes,
            ))
            ->values());

        $this->markScheduleChanged($schedule);

        return $shifts;
    }

    public function update(Shift $shift, array $attributes): Shift
    {
        DB::transaction(function () use ($shift, $attributes): void {
            $targetEmployeeId = $attributes['employee_id'] ?? $shift->employee_id;
            $targetDate = $attributes['date'] ?? $shift->date->toDateString();

            $shift->schedule->shifts()
                ->whereKeyNot($shift->id)
                ->where('employee_id', $targetEmployeeId)
                ->whereDate('date', $targetDate)
                ->delete();

            $shift->update($attributes);
        });

        $this->markScheduleChanged($shift->schedule);

        return $shift->refresh();
    }

    public function delete(Shift $shift): void
    {
        $shift->delete();

        $this->markScheduleChanged($shift->schedule);
    }

    public function deleteMany(Schedule $schedule, array $cells): int
    {
        $deleted = DB::transaction(fn (): int => collect($cells)->sum(
            fn (array $cell): int => $schedule->shifts()
                ->where('employee_id', $cell['employee_id'])
                ->whereDate('date', $cell['date'])
                ->delete(),
        ));

        if ($deleted > 0) {
            $this->markScheduleChanged($schedule);
        }

        return $deleted;
    }

    private function markScheduleChanged(Schedule $schedule): void
    {
        $schedule->markAsDraft();
    }
}
