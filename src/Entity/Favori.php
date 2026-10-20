<?php

namespace App\Entity;

use App\Repository\FavoriRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Un WOD mis de côté par un athlète.
 */
#[ORM\Entity(repositoryClass: FavoriRepository::class)]
#[ORM\Table(name: 'favori')]
#[ORM\UniqueConstraint(name: 'uniq_favori_user_wod', columns: ['user_id', 'wod_id'])]
class Favori
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'favoris')]
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
