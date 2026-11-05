<?php

namespace App\Http\Requests;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class ScheduleShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function schedule(): Schedule
    {
        return $this->route('schedule');
    }

    protected function employeeRules(): array
    {
        return [
            'required',
            'integer',
            'exists:employees,id',
            Rule::exists('employee_schedule', 'employee_id')->where('schedule_id', $this->schedule()->id),
        ];
    }

    protected function dateRules(): array
    {
        return [
            'required',
            'date_format:Y-m-d',
            'after_or_equal:'.$this->schedule()->period_start->toDateString(),
            'before_or_equal:'.$this->schedule()->period_end->toDateString(),
        ];
    }

    protected function timeRules(): array
    {
        return [
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'shift_template_id' => ['nullable', 'integer', 'exists:shift_templates,id'],
        ];
    }

    public function shiftAttributes(): array
    {
        return $this->safe()->only(['start_time', 'end_time', 'shift_template_id']) + ['shift_template_id' => null];
    }
}
