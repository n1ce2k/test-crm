<?php

namespace App\Enums;

enum ScheduleStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::Published => 'Опубликован',
        };
    }
}
