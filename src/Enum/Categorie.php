<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Categorie: string implements TranslatableInterface
{
    case Halterophilie = 'halterophilie';
    case Gymnastique = 'gymnastique';
    case Cardio = 'cardio';
    case Kettlebell = 'kettlebell';
    case Force = 'force';

    public function label(): string
    {
        return match ($this) {
            self::Halterophilie => 'Haltérophilie',
            self::Gymnastique => 'Gymnastique',
            self::Cardio => 'Cardio',
            self::Kettlebell => 'Kettlebell & haltères',
            self::Force => 'Force & accessoires',
        };
    }

    /** Libellé affiché par les formulaires Symfony et EasyAdmin. */
    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $this->label();
    }
}
