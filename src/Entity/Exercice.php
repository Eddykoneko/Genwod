<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Favoris;
use App\Entity\Commentaire;
use App\Entity\Leaderboard;
use App\Entity\LikeDislike;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\HistoriqueExercice;
use App\Repository\ExerciceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: ExerciceRepository::class)]
class Exercice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private ?string $type = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $duree = null;

    #[ORM\Column(length: 255)]
    private ?string $difficulte = null;

    #[ORM\OneToMany(mappedBy: 'exercice_id', targetEntity: HistoriqueExercice::class)]
    private Collection $historiqueExercices;

    #[ORM\OneToMany(mappedBy: 'exercice_id', targetEntity: Leaderboard::class)]
    private Collection $leaderboards;

    #[ORM\OneToMany(mappedBy: 'exercice_id', targetEntity: LikeDislike::class)]
    private Collection $likeDislikes;

    #[ORM\OneToMany(mappedBy: 'exercice_id', targetEntity: Commentaire::class)]
    private Collection $commentaires;

    #[ORM\OneToMany(mappedBy: 'exercice_id', targetEntity: Favoris::class)]
    private Collection $favoris;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->historiqueExercices = new ArrayCollection();
        $this->leaderboards = new ArrayCollection();
        $this->likeDislikes = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->favoris = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDifficulte(): ?string
    {
        return $this->difficulte;
    }

    public function setDifficulte(string $difficulte): self
    {
        $this->difficulte = $difficulte;

        return $this;
    }

    /**
     * @return Collection<int, HistoriqueExercice>
     */
    public function getHistoriqueExercices(): Collection
    {
        return $this->historiqueExercices;
    }

    public function addHistoriqueExercice(HistoriqueExercice $historiqueExercice): self
    {
        if (!$this->historiqueExercices->contains($historiqueExercice)) {
            $this->historiqueExercices[] = $historiqueExercice;
            $historiqueExercice->setExerciceId($this);
        }

        return $this;
    }

    public function removeHistoriqueExercice(HistoriqueExercice $historiqueExercice): self
    {
        if ($this->historiqueExercices->removeElement($historiqueExercice)) {
            // set the owning side to null (unless already changed)
            if ($historiqueExercice->getExerciceId() === $this) {
                $historiqueExercice->setExerciceId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Leaderboard>
     */
    public function getLeaderboards(): Collection
    {
        return $this->leaderboards;
    }

    public function addLeaderboard(Leaderboard $leaderboard): self
    {
        if (!$this->leaderboards->contains($leaderboard)) {
            $this->leaderboards[] = $leaderboard;
            $leaderboard->setExerciceId($this);
        }

        return $this;
    }

    public function removeLeaderboard(Leaderboard $leaderboard): self
    {
        if ($this->leaderboards->removeElement($leaderboard)) {
            // set the owning side to null (unless already changed)
            if ($leaderboard->getExerciceId() === $this) {
                $leaderboard->setExerciceId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LikeDislike>
     */
    public function getLikeDislikes(): Collection
    {
        return $this->likeDislikes;
    }

    public function addLikeDislike(LikeDislike $likeDislike): self
    {
        if (!$this->likeDislikes->contains($likeDislike)) {
            $this->likeDislikes[] = $likeDislike;
            $likeDislike->setExerciceId($this); // Assurez-vous d'avoir cette méthode dans votre classe LikeDislike
        }

        return $this;
    }

    public function removeLikeDislike(LikeDislike $likeDislike): self
    {
        if ($this->likeDislikes->removeElement($likeDislike)) {
            // set the owning side to null (unless already changed)
            if ($likeDislike->getExerciceId() === $this) {
                $likeDislike->setExerciceId(null);
            }
        }

        return $this;
    }

    public function isLikedByUser(User $user): bool
    {
        // Vous pouvez utiliser une fonction de filtrage sur la collection de LikeDislike pour vérifier si l'utilisateur a aimé cet exercice
        return $this->likeDislikes->exists(function ($key, LikeDislike $likeDislike) use ($user) {
            return $likeDislike->getUserId() === $user && $likeDislike->getStatut() === 'like';
        });
    }


    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setExerciceId($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getExerciceId() === $this) {
                $commentaire->setExerciceId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Favoris>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(Favoris $favori): self
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris[] = $favori;
            $favori->setExerciceId($this);
        }

        return $this;
    }

    public function removeFavori(Favoris $favori): self
    {
        if ($this->favoris->removeElement($favori)) {
            // set the owning side to null (unless already changed)
            if ($favori->getExerciceId() === $this) {
                $favori->setExerciceId(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }


}
