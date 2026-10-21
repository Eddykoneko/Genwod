<?php

namespace App\Model;

use App\Enum\Benchmark;
use App\Enum\Difficulte;
use App\Enum\WodType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Critères de recherche de la bibliothèque, alimentés depuis la query string (#[MapQueryString]).
 */
final class WodFiltre
{
    public const TRIS = ['recents', 'populaires', 'courts'];

    public function __construct(
        #[Assert\Length(max: 60)]
        public ?string $q = null,
        public ?WodType $type = null,
        public ?Difficulte $difficulte = null,
        public ?Benchmark $benchmark = null,
        #[Assert\Range(min: 1, max: 90)]
        public ?int $dureeMax = null,
        #[Assert\Choice(choices: self::TRIS)]
        public string $tri = 'recents',
        #[Assert\Positive]
        public int $page = 1,
    ) {
    }

    public function estActif(): bool
    {
        return null !== $this->q || null !== $this->type || null !== $this->difficulte || null !== $this->dureeMax || null !== $this->benchmark;
    }
}
