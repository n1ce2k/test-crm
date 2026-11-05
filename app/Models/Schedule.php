<?php

namespace App\Models;

use App\Enums\ScheduleStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'address', 'status', 'period_start', 'period_end', 'published_at'])]
class Schedule extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ScheduleStatus::class,
            'period_start' => 'date',
            'period_end' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class)->orderBy('last_name')->orderBy('first_name');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where('name', 'like', '%'.trim($search).'%');
    }

    public function scopeWithStatus(Builder $query, ?string $status): Builder
    {
        $status = ScheduleStatus::tryFrom((string) $status);

        return $status ? $query->where('status', $status) : $query;
    }

    public function totalDays(): int
    {
        return (int) $this->period_start->diffInDays($this->period_end) + 1;
    }

    public function progress(): int
    {
        $coveredDays = (int) ($this->covered_days_count ?? $this->shifts()->distinct()->count('date'));

        return (int) round($coveredDays / max($this->totalDays(), 1) * 100);
    }

    public function containsDate(string $date): bool
    {
        return $date >= $this->period_start->toDateString() && $date <= $this->period_end->toDateString();
    }

    public function markAsDraft(): void
    {
        $this->forceFill(['status' => ScheduleStatus::Draft, 'published_at' => null])->touch();
    }

    public function publish(): void
    {
        $this->forceFill(['status' => ScheduleStatus::Published, 'published_at' => now()])->touch();
    }
}
