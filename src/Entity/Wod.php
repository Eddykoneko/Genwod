<?php

namespace App\Entity;

use App\Enum\Benchmark;
use App\Enum\Difficulte;
use App\Enum\WodType;
use App\Repository\WodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WodRepository::class)]
#[ORM\Index(name: 'idx_wod_public_created', columns: ['public', 'created_at'])]
class Wod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    private ?string $titre = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 20, enumType: WodType::class)]
    private WodType $type = WodType::ForTime;

    #[ORM\Column(length: 20, enumType: Difficulte::class)]
    private Difficulte $difficulte = Difficulte::Moyen;

    /** Durée en minutes (time cap pour un For Time). */
    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Range(min: 1, max: 90)]
    private int $duree = 15;

    /** Nombre de tours imposés (For Time uniquement). */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\Range(min: 1, max: 20)]
    private ?int $tours = null;

    /** Schéma de répétitions d'un For Time, ex. « 21-15-9 » (chaque mouvement suit ce schéma). */
    #[ORM\Column(length: 40, nullable: true)]
    #[Assert\Regex(pattern: '/^\d+(-\d+)+$/', message: 'Format attendu : 21-15-9')]
    private ?string $schemaReps = null;

    /** EMOM « circuit » : tous les mouvements chaque minute (ex. Chelsea), au lieu d'une alternance. */
    #[ORM\Column]
    private bool $toutChaqueMinute = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 2000)]
    private ?string $consignes = null;

    /** Famille du WOD de référence (Girls, Hero…), null pour un WOD de la communauté Genwod. */
    #[ORM\Column(length: 20, nullable: true, enumType: Benchmark::class)]
    private ?Benchmark $benchmark = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $auteur = null;

    /** WOD éditorial, proposé par l'équipe Genwod. */
    #[ORM\Column]
    private bool $officiel = false;

    #[ORM\Column]
    private bool $public = true;

    /** Graine du générateur, pour pouvoir reproduire le WOD. */
    #[ORM\Column(length: 32, nullable: true)]
    private ?string $graine = null;

    #[ORM\Column]
    private int $likesCount = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, WodMouvement> */
    #[ORM\OneToMany(targetEntity: WodMouvement::class, mappedBy: 'wod', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Assert\Count(min: 1, minMessage: 'Un WOD contient au moins un mouvement.')]
    #[Assert\Valid]
    private Collection $lignes;

    /** @var Collection<int, Commentaire> */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'wod', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $commentaires;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->lignes = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->titre;
    }

    /** Résumé court du format, ex. « AMRAP 12 min » ou « 5 tours For Time — cap 20 min ». */
    public function getFormat(): string
    {
        return match ($this->type) {
            WodType::ForTime => match (true) {
                null !== $this->schemaReps => $this->schemaReps.' For Time · cap '.$this->duree.' min',
                $this->tours > 1 => $this->tours.' tours For Time · cap '.$this->duree.' min',
                default => 'For Time · cap '.$this->duree.' min',
            },
            WodType::Amrap => 'AMRAP '.$this->duree.' min',
            WodType::Emom => 'EMOM '.$this->duree.' min'.($this->toutChaqueMinute ? ' · tout chaque minute' : ''),
            WodType::Tabata => 'Tabata · '.\count($this->lignes).' × 4 min',
        };
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

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

    public function getType(): WodType
    {
        return $this->type;
    }

    public function setType(WodType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDifficulte(): Difficulte
    {
        return $this->difficulte;
    }

    public function setDifficulte(Difficulte $difficulte): static
    {
        $this->difficulte = $difficulte;

        return $this;
    }

    public function getDuree(): int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getTours(): ?int
    {
        return $this->tours;
    }

    public function setTours(?int $tours): static
    {
        $this->tours = $tours;

        return $this;
    }

    public function getSchemaReps(): ?string
    {
        return $this->schemaReps;
    }

    public function setSchemaReps(?string $schemaReps): static
    {
        $this->schemaReps = $schemaReps;

        return $this;
    }

    public function isToutChaqueMinute(): bool
    {
        return $this->toutChaqueMinute;
    }

    public function setToutChaqueMinute(bool $toutChaqueMinute): static
    {
        $this->toutChaqueMinute = $toutChaqueMinute;

        return $this;
    }

    public function getBenchmark(): ?Benchmark
    {
        return $this->benchmark;
    }

    public function setBenchmark(?Benchmark $benchmark): static
    {
        $this->benchmark = $benchmark;

        return $this;
    }

    public function getConsignes(): ?string
    {
        return $this->consignes;
    }

    public function setConsignes(?string $consignes): static
    {
        $this->consignes = $consignes;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function isOfficiel(): bool
    {
        return $this->officiel;
    }

    public function setOfficiel(bool $officiel): static
    {
        $this->officiel = $officiel;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): static
    {
        $this->public = $public;

        return $this;
    }

    public function getGraine(): ?string
    {
        return $this->graine;
    }

    public function setGraine(?string $graine): static
    {
        $this->graine = $graine;

        return $this;
    }

    public function getLikesCount(): int
    {
        return $this->likesCount;
    }

    public function setLikesCount(int $likesCount): static
    {
        $this->likesCount = max(0, $likesCount);

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /** @return Collection<int, WodMouvement> */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(WodMouvement $ligne): static
    {
        if (!$this->lignes->contains($ligne)) {
            $ligne->setPosition(\count($this->lignes));
            $this->lignes->add($ligne);
            $ligne->setWod($this);
        }

        return $this;
    }

    public function removeLigne(WodMouvement $ligne): static
    {
        if ($this->lignes->removeElement($ligne) && $ligne->getWod() === $this) {
            $ligne->setWod(null);
        }

        return $this;
    }

    /** @return Collection<int, Commentaire> */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }
}
