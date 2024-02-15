<?php

namespace App\Entity;

use App\Repository\FavorisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavorisRepository::class)]
class Favoris
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'favoris', cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_id = null;

    #[ORM\ManyToOne(inversedBy: 'favoris', cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Exercice $exercice_id = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

}
