<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Equipement: string implements TranslatableInterface
{
    case Aucun = 'aucun';
    case Barre = 'barre';
    case Kettlebell = 'kettlebell';
    case Halteres = 'halteres';
    case BarreTraction = 'barre_traction';
    case Rameur = 'rameur';
    case Corde = 'corde';
    case Box = 'box';
    case MedecineBall = 'medecine_ball';
    case Velo = 'velo';
    case Anneaux = 'anneaux';

    public function label(): string
    {
        return match ($this) {
            self::Aucun => 'Poids du corps',
            self::Barre => 'Barre olympique',
            self::Kettlebell => 'Kettlebell',
            self::Halteres => 'Haltères',
            self::BarreTraction => 'Barre de traction',
            self::Rameur => 'Rameur',
            self::Corde => 'Corde à sauter',
            self::Box => 'Box',
            self::MedecineBall => 'Medecine ball',
            self::Velo => 'Assault bike',
            self::Anneaux => 'Anneaux',
        };
    }

    /** Libellé affiché par les formulaires Symfony et EasyAdmin. */
    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $this->label();
    }

    /** @return list<self> Le matériel sélectionnable (le poids du corps est toujours disponible). */
    public static function selectionnables(): array
    {
        return array_values(array_filter(self::cases(), static fn (self $e) => self::Aucun !== $e));
    }
}
