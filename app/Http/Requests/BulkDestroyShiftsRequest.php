<?php

namespace App\Http\Requests;

class BulkDestroyShiftsRequest extends ScheduleShiftRequest
{
    public function rules(): array
    {
        return [
            'cells' => ['required', 'array', 'min:1', 'max:1000'],
            'cells.*.employee_id' => ['required', 'integer'],
            'cells.*.date' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
