<?php

namespace App\Enum;

enum Unite: string
{
    case Reps = 'reps';
    case Calories = 'cal';
    case Metres = 'm';
    case Secondes = 's';

    public function format(int $quantite): string
    {
        return match ($this) {
            self::Reps => (string) $quantite,
            self::Calories => $quantite.' cal',
            self::Metres => $quantite.' m',
            self::Secondes => $quantite.' s',
        };
    }
}
