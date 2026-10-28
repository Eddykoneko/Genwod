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
    case Open = 'open';
    case Classique = 'classique';

    public function label(): string
    {
        return match ($this) {
            self::Girl => 'The Girls',
            self::Hero => 'Hero WOD',
            self::Open => 'CrossFit Open',
            self::Classique => 'Classique',
        };
    }

    /** Libellé court du badge affiché à côté du WOD : « Girl », « Hero »… */
    public function badge(): string
    {
        return match ($this) {
            self::Girl => 'Girl',
            self::Hero => 'Hero',
            self::Open => 'Open',
            self::Classique => 'Classique',
        };
    }

    /** Libellé affiché par les formulaires Symfony et EasyAdmin. */
    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $this->label();
    }
}
