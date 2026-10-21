<?php

namespace App\Entity;

use App\Enum\Unite;
use App\Enum\WodType;
use App\Repository\WodMouvementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Une ligne d'un WOD : « 15 Thrusters (43/30 kg) ».
 */
#[ORM\Entity(repositoryClass: WodMouvementRepository::class)]
class WodMouvement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Wod $wod = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Mouvement $mouvement = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $position = 0;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Positive]
    private int $quantite = 10;

    #[ORM\Column(length: 60, nullable: true)]
    #[Assert\Length(max: 60)]
    private ?string $note = null;

    public function __toString(): string
    {
        return $this->getLibelle();
    }

    public function getLibelle(): string
    {
        $mouvement = $this->mouvement;
        if (null === $mouvement) {
            return '';
        }

        $libelle = $this->getQuantiteLisible().' '.$mouvement->getNom();
        $charge = $this->note ?? $mouvement->getChargeRx();

        return null !== $charge ? $libelle.' ('.$charge.')' : $libelle;
    }

    /**
     * Quantité affichée : le schéma du WOD (« 21-15-9 »), « 8× » en Tabata
     * (maximum de répétitions sur chaque intervalle), sinon la quantité et son unité.
     */
    public function getQuantiteLisible(): string
    {
        return match (true) {
            null !== $this->wod?->getSchemaReps() => (string) $this->wod->getSchemaReps(),
            WodType::Tabata === $this->wod?->getType() => '8×',
            default => $this->getUnite()->format($this->quantite),
        };
    }

    public function getUnite(): Unite
    {
        return $this->mouvement?->getUnite() ?? Unite::Reps;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWod(): ?Wod
    {
        return $this->wod;
    }

    public function setWod(?Wod $wod): static
    {
        $this->wod = $wod;

        return $this;
    }

    public function getMouvement(): ?Mouvement
    {
        return $this->mouvement;
    }

    public function setMouvement(?Mouvement $mouvement): static
    {
        $this->mouvement = $mouvement;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }
}
