<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261021094221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'WODs de référence : benchmark, schéma de répétitions, EMOM tout chaque minute.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wod ADD schema_reps VARCHAR(40) DEFAULT NULL, ADD tout_chaque_minute TINYINT NOT NULL, ADD benchmark VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wod DROP schema_reps, DROP tout_chaque_minute, DROP benchmark');
    }
}
