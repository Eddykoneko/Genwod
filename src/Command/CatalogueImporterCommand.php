<?php

namespace App\Command;

use App\Service\CatalogueImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:catalogue:importer', description: 'Importe le catalogue de mouvements et les WODs officiels (idempotent).')]
final class CatalogueImporterCommand
{
    public function __construct(private readonly CatalogueImporter $importer)
    {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $crees = $this->importer->importer();
        $io->success(\sprintf('%d mouvement(s) et %d WOD(s) officiel(s) créés, le reste a été mis à jour.', $crees['mouvements'], $crees['wods']));

        return 0;
    }
}
