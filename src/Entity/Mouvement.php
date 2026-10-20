<?php

namespace App\Entity;

use App\Enum\Categorie;
use App\Enum\Equipement;
use App\Enum\Unite;
use App\Repository\MouvementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Un mouvement du catalogue (Thruster, Burpee, Rameur…), brique de base des WODs.
 */
#[ORM\Entity(repositoryClass: MouvementRepository::class)]
#[UniqueEntity('slug')]
class Mouvement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    private ?string $nom = null;

    #[ORM\Column(length: 80, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex('/^[a-z0-9-]+$/')]
    private ?string $slug = null;

    #[ORM\Column(length: 20, enumType: Categorie::class)]
    private Categorie $categorie = Categorie::Gymnastique;

    #[ORM\Column(length: 20, enumType: Equipement::class)]
    private Equipement $equipement = Equipement::Aucun;

    #[ORM\Column(length: 10, enumType: Unite::class)]
    private Unite $unite = Unite::Reps;

    /** Volume de référence pour un tour de difficulté « intermédiaire ». */
    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Positive]
    private int $volumeBase = 10;

    /** Charge RX indicative, ex. « 43/30 kg » (homme/femme). */
    #[ORM\Column(length: 40, nullable: true)]
    private ?string $chargeRx = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /** Mouvement très technique : évité par le générateur en difficulté « facile ». */
    #[ORM\Column]
    private bool $technique = false;

    #[ORM\Column]
    private bool $actif = true;

    public function __toString(): string
    {
        return (string) $this->nom;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCategorie(): Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getEquipement(): Equipement
    {
        return $this->equipement;
    }

    public function setEquipement(Equipement $equipement): static
    {
        $this->equipement = $equipement;

        return $this;
    }

    public function getUnite(): Unite
    {
        return $this->unite;
    }

    public function setUnite(Unite $unite): static
    {
        $this->unite = $unite;

        return $this;
    }

    public function getVolumeBase(): int
    {
        return $this->volumeBase;
    }

    public function setVolumeBase(int $volumeBase): static
    {
        $this->volumeBase = $volumeBase;

        return $this;
    }

    public function getChargeRx(): ?string
    {
        return $this->chargeRx;
    }

    public function setChargeRx(?string $chargeRx): static
    {
        $this->chargeRx = $chargeRx;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isTechnique(): bool
    {
        return $this->technique;
    }

    public function setTechnique(bool $technique): static
    {
        $this->technique = $technique;

        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }
}
