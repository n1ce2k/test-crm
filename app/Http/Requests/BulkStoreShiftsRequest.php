<?php

namespace App\Http\Requests;

class BulkStoreShiftsRequest extends ScheduleShiftRequest
{
    public function rules(): array
    {
        return [
            'cells' => ['required', 'array', 'min:1', 'max:1000'],
            'cells.*.employee_id' => $this->employeeRules(),
            'cells.*.date' => $this->dateRules(),
            ...$this->timeRules(),
        ];
    }
}
