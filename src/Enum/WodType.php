<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum WodType: string implements TranslatableInterface
{
    case ForTime = 'for_time';
    case Amrap = 'amrap';
    case Emom = 'emom';
    case DeathBy = 'death_by';

    public function label(): string
    {
        return match ($this) {
            self::ForTime => 'For Time',
            self::Amrap => 'AMRAP',
            self::Emom => 'EMOM',
            self::DeathBy => 'Death By',
        };
    }

    /** Libellé affiché par les formulaires Symfony et EasyAdmin. */
    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $this->label();
    }

    public function explication(): string
    {
        return match ($this) {
            self::ForTime => 'Terminez le travail le plus vite possible, avant le time cap.',
            self::Amrap => 'Un maximum de tours et de répétitions dans le temps imparti.',
            self::Emom => 'Chaque minute, au top : faites le travail, récupérez le reste de la minute.',
            self::DeathBy => 'Le nombre de répétitions augmente à chaque minute, jusqu\'à ne plus pouvoir finir dans la minute.',
        };
    }

    /** Un temps plus court est meilleur pour un For Time ; sinon, plus de répétitions = mieux. */
    public function scoreAuTemps(): bool
    {
        return self::ForTime === $this;
    }

    /** @return array<string, self> */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->label()] = $case;
        }

        return $choices;
    }
}
