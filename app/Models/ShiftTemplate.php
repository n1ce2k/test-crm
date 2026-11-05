<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'start_time', 'end_time', 'color', 'sort'])]
class ShiftTemplate extends Model
{
    use HasFactory;

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}
