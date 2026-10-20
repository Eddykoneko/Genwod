<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Difficulte: string implements TranslatableInterface
{
    case Facile = 'facile';
    case Moyen = 'moyen';
    case Difficile = 'difficile';

    public function label(): string
    {
        return match ($this) {
            self::Facile => 'Facile',
            self::Moyen => 'Intermédiaire',
            self::Difficile => 'Difficile',
        };
    }

    /** Libellé affiché par les formulaires Symfony et EasyAdmin. */
    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $this->label();
    }

    /** Multiplicateur appliqué aux volumes de base des mouvements. */
    public function facteurVolume(): float
    {
        return match ($this) {
            self::Facile => 0.7,
            self::Moyen => 1.0,
            self::Difficile => 1.4,
        };
    }

    /** Nombre de « flammes » affichées dans l'interface. */
    public function intensite(): int
    {
        return match ($this) {
            self::Facile => 1,
            self::Moyen => 2,
            self::Difficile => 3,
        };
    }

    public function bonusPoints(): int
    {
        return match ($this) {
            self::Facile => 0,
            self::Moyen => 5,
            self::Difficile => 10,
        };
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
