<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ScheduleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $schedules = Schedule::query()
            ->search($request->string('search')->toString())
            ->withStatus($request->string('status')->toString())
            ->select('schedules.*')
            ->addSelect([
                'covered_days_count' => Shift::query()
                    ->selectRaw('count(distinct date)')
                    ->whereColumn('shifts.schedule_id', 'schedules.id'),
            ])
            ->withCount('employees')
            ->latest('updated_at')
            ->get();

        return ScheduleResource::collection($schedules);
    }

    public function store(StoreScheduleRequest $request): ScheduleResource
    {
        $periodStart = Carbon::createFromFormat('Y-m', $request->validated('month'))->startOfMonth();

        $schedule = DB::transaction(function () use ($request, $periodStart): Schedule {
            $schedule = Schedule::query()->create([
                'name' => $request->validated('name'),
                'address' => $request->validated('address'),
                'status' => ScheduleStatus::Draft,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodStart->copy()->endOfMonth()->toDateString(),
            ]);

            $schedule->employees()->sync($request->validated('employee_ids'));

            return $schedule;
        });

        return new ScheduleResource($schedule->load('employees'));
    }

    public function show(Schedule $schedule): ScheduleResource
    {
        return new ScheduleResource($schedule->load('employees')->loadCount('employees'));
    }

    public function save(Schedule $schedule): ScheduleResource
    {
        $schedule->markAsDraft();

        return $this->show($schedule);
    }

    public function publish(Schedule $schedule): ScheduleResource
    {
        $schedule->publish();

        return $this->show($schedule);
    }
}
