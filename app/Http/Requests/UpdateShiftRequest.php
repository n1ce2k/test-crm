<?php

namespace App\Http\Requests;

class UpdateShiftRequest extends ScheduleShiftRequest
{
    public function rules(): array
    {
        return [
            'employee_id' => ['sometimes', ...$this->employeeRules()],
            'date' => ['sometimes', ...$this->dateRules()],
            ...$this->timeRules(),
        ];
    }
}
