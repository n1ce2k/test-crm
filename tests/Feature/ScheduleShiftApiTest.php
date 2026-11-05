<?php

namespace Tests\Feature;

use App\Enums\ScheduleStatus;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\ShiftTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleShiftApiTest extends TestCase
{
    use RefreshDatabase;

    private Schedule $schedule;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schedule = Schedule::factory()->published()->create([
            'period_start' => '2026-11-01',
            'period_end' => '2026-11-30',
        ]);
        $this->employee = Employee::factory()->create();
        $this->schedule->employees()->attach($this->employee);
    }

    public function test_shifts_are_returned_for_requested_period(): void
    {
        Shift::factory()->for($this->schedule)->for($this->employee)->create(['date' => '2026-11-02']);
        Shift::factory()->for($this->schedule)->for($this->employee)->create(['date' => '2026-11-20']);

        $this->getJson("/api/v1/schedules/{$this->schedule->id}/shifts?from=2026-11-01&to=2026-11-08")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.date', '2026-11-02')
            ->assertJsonPath('data.0.duration_minutes', 540);
    }

    public function test_shift_can_be_assigned_from_template(): void
    {
        $template = ShiftTemplate::factory()->create(['start_time' => '07:00', 'end_time' => '15:00']);

        $this->postJson("/api/v1/schedules/{$this->schedule->id}/shifts", [
            'employee_id' => $this->employee->id,
            'date' => '2026-11-05',
            'start_time' => '07:00',
            'end_time' => '15:00',
            'shift_template_id' => $template->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.shift_template_id', $template->id)
            ->assertJsonPath('data.start_time', '07:00');

        $this->assertSame(ScheduleStatus::Draft, $this->schedule->refresh()->status);
    }

    public function test_assigning_to_occupied_cell_replaces_shift(): void
    {
        Shift::factory()->for($this->schedule)->for($this->employee)->create(['date' => '2026-11-05']);

        $this->postJson("/api/v1/schedules/{$this->schedule->id}/shifts", [
            'employee_id' => $this->employee->id,
            'date' => '2026-11-05',
            'start_time' => '10:00',
            'end_time' => '18:00',
        ])->assertCreated();

        $this->assertDatabaseCount('shifts', 1);
        $this->assertSame('10:00', substr(Shift::first()->start_time, 0, 5));
    }

    public function test_shift_time_and_employee_are_validated(): void
    {
        $stranger = Employee::factory()->create();

        $this->postJson("/api/v1/schedules/{$this->schedule->id}/shifts", [
            'employee_id' => $stranger->id,
            'date' => '2026-12-01',
            'start_time' => '18:00',
            'end_time' => '09:00',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['employee_id', 'date', 'end_time']);

        $this->assertDatabaseEmpty('shifts');
    }

    public function test_shift_can_be_updated_and_moved(): void
    {
        $shift = Shift::factory()->for($this->schedule)->for($this->employee)->create(['date' => '2026-11-05']);
        $occupied = Shift::factory()->for($this->schedule)->for($this->employee)->create(['date' => '2026-11-06']);

        $this->patchJson("/api/v1/schedules/{$this->schedule->id}/shifts/{$shift->id}", [
            'date' => '2026-11-06',
            'start_time' => '12:00',
            'end_time' => '20:00',
        ])
            ->assertOk()
            ->assertJsonPath('data.date', '2026-11-06')
            ->assertJsonPath('data.end_time', '20:00');

        $this->assertModelMissing($occupied);
        $this->assertDatabaseCount('shifts', 1);
    }

    public function test_shift_from_another_schedule_cannot_be_updated(): void
    {
        $foreignShift = Shift::factory()->create();

        $this->patchJson("/api/v1/schedules/{$this->schedule->id}/shifts/{$foreignShift->id}", [
            'start_time' => '12:00',
            'end_time' => '20:00',
        ])->assertNotFound();
    }

    public function test_shift_can_be_deleted(): void
    {
        $shift = Shift::factory()->for($this->schedule)->for($this->employee)->create(['date' => '2026-11-05']);

        $this->deleteJson("/api/v1/schedules/{$this->schedule->id}/shifts/{$shift->id}")->assertNoContent();

        $this->assertModelMissing($shift);
    }

    public function test_shifts_can_be_assigned_and_cleared_in_bulk(): void
    {
        $colleague = Employee::factory()->create();
        $this->schedule->employees()->attach($colleague);

        $cells = [
            ['employee_id' => $this->employee->id, 'date' => '2026-11-10'],
            ['employee_id' => $colleague->id, 'date' => '2026-11-10'],
            ['employee_id' => $colleague->id, 'date' => '2026-11-11'],
        ];

        $this->postJson("/api/v1/schedules/{$this->schedule->id}/shifts/bulk", [
            'cells' => $cells,
            'start_time' => '08:00',
            'end_time' => '16:00',
        ])
            ->assertOk()
            ->assertJsonCount(3, 'data');

        $this->deleteJson("/api/v1/schedules/{$this->schedule->id}/shifts/bulk", ['cells' => array_slice($cells, 0, 2)])
            ->assertOk()
            ->assertJsonPath('deleted', 2);

        $this->assertDatabaseCount('shifts', 1);
    }
}
