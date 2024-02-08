<?php

namespace App\Entity;

use App\Repository\ExerciceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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

    public function setType(string $type): static
    {
        $this->type = $type;

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


    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDifficulte(): ?string
    {
        return $this->difficulte;
    }

    public function setDifficulte(string $difficulte): static
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

    public function addHistoriqueExercice(HistoriqueExercice $historiqueExercice): static
    {
        if (!$this->historiqueExercices->contains($historiqueExercice)) {
            $this->historiqueExercices->add($historiqueExercice);
            $historiqueExercice->setExerciceId($this);
        }

        return $this;
    }

    public function removeHistoriqueExercice(HistoriqueExercice $historiqueExercice): static
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

    public function addLeaderboard(Leaderboard $leaderboard): static
    {
        if (!$this->leaderboards->contains($leaderboard)) {
            $this->leaderboards->add($leaderboard);
            $leaderboard->setExerciceId($this);
        }

        return $this;
    }

    public function removeLeaderboard(Leaderboard $leaderboard): static
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

    public function addLikeDislike(LikeDislike $likeDislike): static
    {
        if (!$this->likeDislikes->contains($likeDislike)) {
            $this->likeDislikes->add($likeDislike);
            $likeDislike->setExerciceId($this);
        }

        return $this;
    }

    public function removeLikeDislike(LikeDislike $likeDislike): static
    {
        if ($this->likeDislikes->removeElement($likeDislike)) {
            // set the owning side to null (unless already changed)
            if ($likeDislike->getExerciceId() === $this) {
                $likeDislike->setExerciceId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setExerciceId($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
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

    public function addFavori(Favoris $favori): static
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
            $favori->setExerciceId($this);
        }

        return $this;
    }

    public function removeFavori(Favoris $favori): static
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

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
