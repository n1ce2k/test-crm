<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['first_name', 'last_name', 'position', 'avatar'])]
class Employee extends Model
{
    use HasFactory;

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function fullName(): string
    {
        return trim("{$this->last_name} {$this->first_name}");
    }
}
