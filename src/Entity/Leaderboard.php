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

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $score = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user): self
    {
        $this->user_id = $user;

        return $this;
    }

    public function getExerciceId(): ?Exercice
    {
        return $this->exercice_id;
    }

    public function setExerciceId(?Exercice $exercice): self
    {
        $this->exercice_id = $exercice;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }
}