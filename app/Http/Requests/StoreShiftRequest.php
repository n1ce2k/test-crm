<?php

namespace App\Http\Requests;

class StoreShiftRequest extends ScheduleShiftRequest
{
    public function rules(): array
    {
        return [
            'employee_id' => $this->employeeRules(),
            'date' => $this->dateRules(),
            ...$this->timeRules(),
        ];
    }
}
