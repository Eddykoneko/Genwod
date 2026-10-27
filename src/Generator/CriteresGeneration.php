<?php

namespace App\Generator;

use App\Enum\Difficulte;
use App\Enum\Equipement;
use App\Enum\WodType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Paramètres du générateur. Avec la même graine et les mêmes critères,
 * le générateur produit toujours le même WOD : l'URL est donc partageable.
 */
final class CriteresGeneration
{
    /**
     * @param list<Equipement> $equipements
     */
    public function __construct(
        public WodType $type = WodType::Amrap,
        #[Assert\Range(min: 4, max: 40)]
        public int $duree = 12,
        public Difficulte $difficulte = Difficulte::Moyen,
        public array $equipements = [],
        #[Assert\Regex('/^[a-f0-9]{8}$/')]
        public ?string $graine = null,
    ) {
        $this->graine ??= self::nouvelleGraine();
    }

    public static function nouvelleGraine(): string
    {
        return bin2hex(random_bytes(4));
    }

    public function avecNouvelleGraine(): self
    {
        $copie = clone $this;
        $copie->graine = self::nouvelleGraine();

        return $copie;
    }

    /** @return array<string, mixed> Paramètres de query string reproduisant ces critères. */
    public function versQuery(): array
    {
        return [
            'type' => $this->type->value,
            'duree' => $this->duree,
            'difficulte' => $this->difficulte->value,
            'materiel' => array_map(static fn (Equipement $e) => $e->value, $this->equipements),
            'graine' => $this->graine,
        ];
    }

    /** @param array<string, mixed> $query */
    public static function depuisQuery(array $query): self
    {
        $materiel = \is_array($query['materiel'] ?? null) ? $query['materiel'] : [];
        $type = WodType::tryFrom((string) ($query['type'] ?? ''));
        $graine = \is_string($query['graine'] ?? null) && preg_match('/^[a-f0-9]{8}$/', $query['graine']) ? $query['graine'] : null;

        return new self(
            type: $type?->isGenerable() ? $type : WodType::Amrap,
            duree: max(4, min(40, (int) ($query['duree'] ?? 12))),
            difficulte: Difficulte::tryFrom((string) ($query['difficulte'] ?? '')) ?? Difficulte::Moyen,
            equipements: array_values(array_filter(array_map(
                static fn ($v) => \is_string($v) ? Equipement::tryFrom($v) : null,
                $materiel,
            ))),
            graine: $graine,
        );
    }
}
