<?php

namespace App\Entity;

use App\Repository\HistoriqueExerciceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueExerciceRepository::class)]
class HistoriqueExercice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    private ?string $mode = null;

    #[ORM\Column]
    private ?int $nombreTours = null;

    #[ORM\Column]
    private ?int $nombreRepetition = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $temps = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(inversedBy: 'historiqueExercices', cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_id = null;

    #[ORM\ManyToOne(inversedBy: 'historiqueExercices', cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Exercice $exercice_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(string $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    public function getNombreTours(): ?int
    {
        return $this->nombreTours;
    }

    public function setNombreTours(int $nombreTours): static
    {
        $this->nombreTours = $nombreTours;

        return $this;
    }

    public function getNombreRepetition(): ?int
    {
        return $this->nombreRepetition;
    }

    public function setNombreRepetition(int $nombreRepetition): static
    {
        $this->nombreRepetition = $nombreRepetition;

        return $this;
    }

    public function getTemps(): ?\DateTimeInterface
    {
        return $this->temps;
    }

    public function setTemps(\DateTimeInterface $temps): static
    {
        $this->temps = $temps;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getExerciceId(): ?Exercice
    {
        return $this->exercice_id;
    }

    public function setExerciceId(?Exercice $exercice_id): static
    {
        $this->exercice_id = $exercice_id;

        return $this;
    }
}
