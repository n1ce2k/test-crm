<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['schedule_id', 'employee_id', 'shift_template_id', 'date', 'start_time', 'end_time'])]
class Shift extends Model
{
    use HasFactory;

    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn (string $value): Carbon => Carbon::parse($value)->startOfDay(),
            set: fn (mixed $value): string => Carbon::parse($value)->toDateString(),
        );
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ShiftTemplate::class, 'shift_template_id');
    }

    public function durationInMinutes(): int
    {
        return (int) Carbon::parse($this->start_time)->diffInMinutes(Carbon::parse($this->end_time));
    }
}
