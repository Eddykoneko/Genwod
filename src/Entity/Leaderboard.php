<?php

namespace App\Entity;

use App\Repository\LeaderboardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeaderboardRepository::class)]
class Leaderboard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToOne(inversedBy: 'leaderboards')]
    #[ORM\JoinColumn(onDelete: "CASCADE", nullable: false)]
    private ?User $user_id = null;

    #[ORM\ManyToOne(inversedBy: 'leaderboards')]
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
}
