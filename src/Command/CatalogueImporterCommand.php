<?php

namespace App\Command;

use App\Service\CatalogueImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:catalogue:importer', description: 'Importe le catalogue de mouvements et les WODs de référence (idempotent).')]
final class CatalogueImporterCommand
{
    public function __construct(private readonly CatalogueImporter $importer)
    {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $crees = $this->importer->importer();
        $io->success(\sprintf(
            '%d mouvement(s) et %d WOD(s) de référence créés, le reste a été mis à jour. %d ancien(s) WOD(s) officiel(s) retiré(s) de la sélection.',
            $crees['mouvements'], $crees['wods'], $crees['retires'],
        ));

        return 0;
    }
}
