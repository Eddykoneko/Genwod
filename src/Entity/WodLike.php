<?php

namespace App\Entity;

use App\Repository\WodLikeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Un « j'aime » sur un WOD (table wod_like : LIKE est un mot réservé SQL).
 */
#[ORM\Entity(repositoryClass: WodLikeRepository::class)]
#[ORM\Table(name: 'wod_like')]
#[ORM\UniqueConstraint(name: 'uniq_wod_like_user_wod', columns: ['user_id', 'wod_id'])]
class WodLike
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Wod $wod;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(User $user, Wod $wod)
    {
        $this->user = $user;
        $this->wod = $wod;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getWod(): Wod
    {
        return $this->wod;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
