<?php

namespace App\Entity;

use App\Enum\Mode;
use App\Enum\WodType;
use App\Repository\SeanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Le résultat d'un athlète sur un WOD (remplace HistoriqueExercice + Leaderboard de la v1).
 */
#[ORM\Entity(repositoryClass: SeanceRepository::class)]
#[ORM\Index(name: 'idx_seance_date', columns: ['realisee_le'])]
class Seance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'seances')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $athlete = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Wod $wod = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    #[Assert\LessThanOrEqual('today', message: 'La séance ne peut pas être dans le futur.')]
    private ?\DateTimeImmutable $realiseeLe = null;

    #[ORM\Column(length: 10, enumType: Mode::class)]
    private Mode $mode = Mode::Rx;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 1, max: 10800)]
    private ?int $tempsSecondes = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\Range(min: 0, max: 500)]
    private ?int $tours = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 5000)]
    private ?int $reps = null;

    /** Ressenti de l'effort (RPE simplifié) de 1 à 5. */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\Range(min: 1, max: 5)]
    private ?int $ressenti = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 500)]
    private ?string $notes = null;

    #[ORM\Column]
    private int $points = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->realiseeLe = new \DateTimeImmutable('today');
    }

    #[Assert\Callback]
    public function validerScore(ExecutionContextInterface $context): void
    {
        $type = $this->wod?->getType();
        if (WodType::ForTime === $type && null === $this->tempsSecondes) {
            $context->buildViolation('Indiquez votre temps final.')->atPath('tempsSecondes')->addViolation();
        }
        if (WodType::Amrap === $type && null === $this->tours && null === $this->reps) {
            $context->buildViolation('Indiquez vos tours et/ou répétitions.')->atPath('tours')->addViolation();
        }
        if (\in_array($type, [WodType::Emom, WodType::Tabata], true) && null === $this->reps) {
            $context->buildViolation('Indiquez le total de répétitions.')->atPath('reps')->addViolation();
        }
    }

    /**
     * Valeur de tri du classement : plus petite = meilleure pour un For Time,
     * plus grande = meilleure sinon (tours × 1000 + reps).
     */
    public function getValeurScore(): int
    {
        if (WodType::ForTime === $this->wod?->getType()) {
            return $this->tempsSecondes ?? \PHP_INT_MAX;
        }

        return ($this->tours ?? 0) * 1000 + ($this->reps ?? 0);
    }

    /** Score lisible : « 12:34 », « 7 tours + 12 reps » ou « 184 reps ». */
    public function getScoreLisible(): string
    {
        return match ($this->wod?->getType()) {
            WodType::ForTime => self::formaterTemps($this->tempsSecondes ?? 0),
            WodType::Amrap => \sprintf('%d tours + %d reps', $this->tours ?? 0, $this->reps ?? 0),
            default => \sprintf('%d reps', $this->reps ?? 0),
        };
    }

    public static function formaterTemps(int $secondes): string
    {
        return \sprintf('%d:%02d', intdiv($secondes, 60), $secondes % 60);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAthlete(): ?User
    {
        return $this->athlete;
    }

    public function setAthlete(?User $athlete): static
    {
        $this->athlete = $athlete;

        return $this;
    }

    public function getWod(): ?Wod
    {
        return $this->wod;
    }

    public function setWod(?Wod $wod): static
    {
        $this->wod = $wod;

        return $this;
    }

    public function getRealiseeLe(): ?\DateTimeImmutable
    {
        return $this->realiseeLe;
    }

    public function setRealiseeLe(?\DateTimeImmutable $realiseeLe): static
    {
        $this->realiseeLe = $realiseeLe;

        return $this;
    }

    public function getMode(): Mode
    {
        return $this->mode;
    }

    public function setMode(Mode $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    public function getTempsSecondes(): ?int
    {
        return $this->tempsSecondes;
    }

    public function setTempsSecondes(?int $tempsSecondes): static
    {
        $this->tempsSecondes = $tempsSecondes;

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

    public function getReps(): ?int
    {
        return $this->reps;
    }

    public function setReps(?int $reps): static
    {
        $this->reps = $reps;

        return $this;
    }

    public function getRessenti(): ?int
    {
        return $this->ressenti;
    }

    public function setRessenti(?int $ressenti): static
    {
        $this->ressenti = $ressenti;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
