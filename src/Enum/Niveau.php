<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Niveau: string implements TranslatableInterface
{
    case Debutant = 'debutant';
    case Intermediaire = 'intermediaire';
    case Confirme = 'confirme';

    public function label(): string
    {
        return match ($this) {
            self::Debutant => 'Débutant',
            self::Intermediaire => 'Intermédiaire',
            self::Confirme => 'Confirmé',
        };
    }

    /** Libellé affiché par les formulaires Symfony et EasyAdmin. */
    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $this->label();
    }

    public function difficulteConseillee(): Difficulte
    {
        return match ($this) {
            self::Debutant => Difficulte::Facile,
            self::Intermediaire => Difficulte::Moyen,
            self::Confirme => Difficulte::Difficile,
        };
    }
}
