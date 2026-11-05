<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->fullName(),
            'initials' => Str::upper(Str::substr($this->last_name, 0, 1).Str::substr($this->first_name, 0, 1)),
            'position' => $this->position,
            'avatar' => $this->avatar,
        ];
    }
}
