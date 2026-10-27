<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Scores en charge (For Load) et WODs par intervalles (Fight Gone Bad, The Chief).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE seance ADD charge_kg DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE wod ADD blocs SMALLINT DEFAULT NULL, ADD travail_secondes SMALLINT DEFAULT NULL, ADD repos_secondes SMALLINT DEFAULT NULL, ADD par_station TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE seance DROP charge_kg');
        $this->addSql('ALTER TABLE wod DROP blocs, DROP travail_secondes, DROP repos_secondes, DROP par_station');
    }
}
