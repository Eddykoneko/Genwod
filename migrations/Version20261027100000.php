<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fin du Tabata et des WODs « classiques » : les WODs existants deviennent des AMRAP communautaires.';
    }

    public function up(Schema $schema): void
    {
        // Tabata Something Else était le seul WOD « classique » : il reste en ligne, hors sélection officielle.
        $this->addSql("UPDATE wod SET benchmark = NULL, officiel = 0 WHERE benchmark = 'classique'");
        // Comme un AMRAP, un Tabata se scorait en répétitions : les séances restent valides.
        $this->addSql("UPDATE wod SET type = 'amrap' WHERE type = 'tabata'");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Le type d\'origine des WODs Tabata n\'est pas conservé.');
    }
}
