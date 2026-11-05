<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkDestroyShiftsRequest;
use App\Http\Requests\BulkStoreShiftsRequest;
use App\Http\Requests\IndexShiftsRequest;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Http\Resources\ShiftResource;
use App\Models\Schedule;
use App\Models\Shift;
use App\Services\ShiftAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ScheduleShiftController extends Controller
{
    public function __construct(private ShiftAssignmentService $assignments) {}

    public function index(IndexShiftsRequest $request, Schedule $schedule): AnonymousResourceCollection
    {
        $shifts = $schedule->shifts()
            ->whereBetween('date', [$request->validated('from'), $request->validated('to')])
            ->orderBy('date')
            ->get();

        return ShiftResource::collection($shifts);
    }

    public function store(StoreShiftRequest $request, Schedule $schedule): JsonResponse
    {
        $shift = $this->assignments->assign(
            $schedule,
            $request->integer('employee_id'),
            $request->validated('date'),
            $request->shiftAttributes(),
        );

        return (new ShiftResource($shift))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateShiftRequest $request, Schedule $schedule, Shift $shift): ShiftResource
    {
        $attributes = $request->shiftAttributes() + $request->safe()->only(['employee_id', 'date']);

        return new ShiftResource($this->assignments->update($shift, $attributes));
    }

    public function destroy(Schedule $schedule, Shift $shift): Response
    {
        $this->assignments->delete($shift);

        return response()->noContent();
    }

    public function bulkStore(BulkStoreShiftsRequest $request, Schedule $schedule): AnonymousResourceCollection
    {
        $shifts = $this->assignments->assignMany(
            $schedule,
            $request->validated('cells'),
            $request->shiftAttributes(),
        );

        return ShiftResource::collection($shifts);
    }

    public function bulkDestroy(BulkDestroyShiftsRequest $request, Schedule $schedule): JsonResponse
    {
        $deleted = $this->assignments->deleteMany($schedule, $request->validated('cells'));

        return response()->json(['deleted' => $deleted]);
    }
}
