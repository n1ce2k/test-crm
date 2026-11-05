<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'period_start' => $this->period_start->toDateString(),
            'period_end' => $this->period_end->toDateString(),
            'published_at' => $this->published_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'employees_count' => $this->employees_count ?? $this->employees()->count(),
            'progress' => $this->progress(),
            'employees' => EmployeeResource::collection($this->whenLoaded('employees')),
        ];
    }
}
