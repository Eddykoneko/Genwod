<?php

namespace App\Command;

use App\Enum\Difficulte;
use App\Enum\Equipement;
use App\Enum\WodType;
use App\Generator\CriteresGeneration;
use App\Generator\WodGenerator;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:generer', description: 'Génère un WOD dans le terminal (sans l\'enregistrer).')]
final class GenererCommand
{
    public function __construct(private readonly WodGenerator $generateur)
    {
    }

    /** @param list<string> $materiel */
    public function __invoke(
        SymfonyStyle $io,
        #[Argument('for_time, amrap, emom ou death_by')] string $type = 'amrap',
        #[Option('Durée en minutes')] int $duree = 12,
        #[Option('facile, moyen ou difficile')] string $difficulte = 'moyen',
        #[Option('Matériel disponible (répétable)')] array $materiel = [],
        #[Option('Graine pour reproduire un WOD')] ?string $graine = null,
    ): int {
        $criteres = new CriteresGeneration(
            WodType::from($type),
            $duree,
            Difficulte::from($difficulte),
            array_map(static fn (string $m) => Equipement::from($m), $materiel),
            $graine,
        );
        $wod = $this->generateur->generer($criteres);

        $io->title($wod->getTitre().' — '.$wod->getFormat());
        $io->listing(array_map(static fn ($l) => $l->getLibelle(), $wod->getLignes()->toArray()));
        $io->text((string) $wod->getConsignes());
        $io->comment('Graine : '.$criteres->graine);

        return 0;
    }
}
