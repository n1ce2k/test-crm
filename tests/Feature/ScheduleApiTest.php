<?php

namespace Tests\Feature;

use App\Enums\ScheduleStatus;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedules_can_be_filtered_by_search_and_status(): void
    {
        Schedule::factory()->published()->create(['name' => 'ТЦ Метрополис']);
        Schedule::factory()->create(['name' => 'ТЦ Авиапарк']);
        Schedule::factory()->create(['name' => 'ТЦ Метро Сити']);

        $this->getJson('/api/v1/schedules?search=Метро')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/v1/schedules?search=Метро&status=draft')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'ТЦ Метро Сити');
    }

    public function test_schedule_list_contains_progress_and_employees_count(): void
    {
        $schedule = Schedule::factory()->create([
            'period_start' => '2026-11-01',
            'period_end' => '2026-11-30',
        ]);
        $employees = Employee::factory()->count(2)->create();
        $schedule->employees()->sync($employees);

        foreach (['2026-11-01', '2026-11-02', '2026-11-03'] as $date) {
            Shift::factory()->for($schedule)->for($employees[0])->create(['date' => $date]);
        }

        Shift::factory()->for($schedule)->for($employees[1])->create(['date' => '2026-11-01']);

        $this->getJson('/api/v1/schedules')
            ->assertOk()
            ->assertJsonPath('data.0.employees_count', 2)
            ->assertJsonPath('data.0.progress', 10);
    }

    public function test_schedule_can_be_created_for_a_month(): void
    {
        $employees = Employee::factory()->count(3)->create();

        $response = $this->postJson('/api/v1/schedules', [
            'name' => 'ТЦ Европейский',
            'address' => 'пл. Киевского Вокзала, 2',
            'month' => '2026-02',
            'employee_ids' => $employees->pluck('id')->all(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.period_start', '2026-02-01')
            ->assertJsonPath('data.period_end', '2026-02-28')
            ->assertJsonCount(3, 'data.employees');
    }

    public function test_schedule_creation_is_validated(): void
    {
        $this->postJson('/api/v1/schedules', ['month' => '02-2026', 'employee_ids' => [999]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'address', 'month', 'employee_ids.0']);
    }

    public function test_schedule_can_be_published_and_saved_as_draft(): void
    {
        $schedule = Schedule::factory()->create();

        $this->postJson("/api/v1/schedules/{$schedule->id}/publish")
            ->assertOk()
            ->assertJsonPath('data.status', 'published');

        $this->assertSame(ScheduleStatus::Published, $schedule->refresh()->status);
        $this->assertNotNull($schedule->published_at);

        $this->postJson("/api/v1/schedules/{$schedule->id}/save")
            ->assertOk()
            ->assertJsonPath('data.status', 'draft');

        $this->assertNull($schedule->refresh()->published_at);
    }

    public function test_missing_schedule_returns_not_found(): void
    {
        $this->getJson('/api/v1/schedules/999')->assertNotFound();
    }
}
