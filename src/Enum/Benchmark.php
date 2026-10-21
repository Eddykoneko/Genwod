<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Famille d'un WOD de référence de la communauté CrossFit.
 */
enum Benchmark: string implements TranslatableInterface
{
    case Girl = 'girl';
    case Hero = 'hero';
    case Classique = 'classique';

    public function label(): string
    {
        return match ($this) {
            self::Girl => 'The Girls',
            self::Hero => 'Hero WOD',
            self::Classique => 'Classique',
        };
    }

    /** Libellé affiché par les formulaires Symfony et EasyAdmin. */
    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $this->label();
    }
}
