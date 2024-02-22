<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240220214528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exercice ADD mode VARCHAR(60) NOT NULL, CHANGE score score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE leaderboard DROP mode, DROP score');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exercice DROP mode, CHANGE score score VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE leaderboard ADD mode VARCHAR(60) NOT NULL, ADD score INT NOT NULL');
    }
}
