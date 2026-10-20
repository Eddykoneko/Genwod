<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261020091353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Genwod v2 : schéma initial (WODs, mouvements, séances, interactions, comptes).';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, contenu LONGTEXT NOT NULL, created_at DATETIME NOT NULL, auteur_id INT NOT NULL, wod_id INT NOT NULL, INDEX IDX_67F068BC60BB6FE6 (auteur_id), INDEX IDX_67F068BC91C30030 (wod_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE favori (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, wod_id INT NOT NULL, UNIQUE INDEX uniq_favori_user_wod (user_id, wod_id), INDEX IDX_EF85A2CCA76ED395 (user_id), INDEX IDX_EF85A2CC91C30030 (wod_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE mouvement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(80) NOT NULL, slug VARCHAR(80) NOT NULL, categorie VARCHAR(20) NOT NULL, equipement VARCHAR(20) NOT NULL, unite VARCHAR(10) NOT NULL, volume_base SMALLINT NOT NULL, charge_rx VARCHAR(40) DEFAULT NULL, description LONGTEXT DEFAULT NULL, technique TINYINT NOT NULL, actif TINYINT NOT NULL, UNIQUE INDEX UNIQ_5B51FC3E989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE seance (id INT AUTO_INCREMENT NOT NULL, realisee_le DATE NOT NULL, mode VARCHAR(10) NOT NULL, temps_secondes INT DEFAULT NULL, tours SMALLINT DEFAULT NULL, reps INT DEFAULT NULL, ressenti SMALLINT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, points INT NOT NULL, created_at DATETIME NOT NULL, athlete_id INT NOT NULL, wod_id INT NOT NULL, INDEX idx_seance_date (realisee_le), INDEX IDX_DF7DFD0EFE6BCB8B (athlete_id), INDEX IDX_DF7DFD0E91C30030 (wod_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, pseudo VARCHAR(30) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT NOT NULL, niveau VARCHAR(20) DEFAULT NULL, bio VARCHAR(160) DEFAULT NULL, taille SMALLINT DEFAULT NULL, poids SMALLINT DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D64986CC499D (pseudo), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE wod (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(80) NOT NULL, slug VARCHAR(100) NOT NULL, type VARCHAR(20) NOT NULL, difficulte VARCHAR(20) NOT NULL, duree SMALLINT NOT NULL, tours SMALLINT DEFAULT NULL, consignes LONGTEXT DEFAULT NULL, officiel TINYINT NOT NULL, public TINYINT NOT NULL, graine VARCHAR(32) DEFAULT NULL, likes_count INT NOT NULL, created_at DATETIME NOT NULL, auteur_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_64575EE989D9B62 (slug), INDEX idx_wod_public_created (public, created_at), INDEX IDX_64575EE60BB6FE6 (auteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE wod_like (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, wod_id INT NOT NULL, UNIQUE INDEX uniq_wod_like_user_wod (user_id, wod_id), INDEX IDX_1A48F115A76ED395 (user_id), INDEX IDX_1A48F11591C30030 (wod_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE wod_mouvement (id INT AUTO_INCREMENT NOT NULL, position SMALLINT NOT NULL, quantite SMALLINT NOT NULL, note VARCHAR(60) DEFAULT NULL, wod_id INT NOT NULL, mouvement_id INT NOT NULL, INDEX IDX_877F1FC891C30030 (wod_id), INDEX IDX_877F1FC8ECD1C222 (mouvement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC91C30030 FOREIGN KEY (wod_id) REFERENCES wod (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favori ADD CONSTRAINT FK_EF85A2CCA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favori ADD CONSTRAINT FK_EF85A2CC91C30030 FOREIGN KEY (wod_id) REFERENCES wod (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0EFE6BCB8B FOREIGN KEY (athlete_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0E91C30030 FOREIGN KEY (wod_id) REFERENCES wod (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wod ADD CONSTRAINT FK_64575EE60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE wod_like ADD CONSTRAINT FK_1A48F115A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wod_like ADD CONSTRAINT FK_1A48F11591C30030 FOREIGN KEY (wod_id) REFERENCES wod (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wod_mouvement ADD CONSTRAINT FK_877F1FC891C30030 FOREIGN KEY (wod_id) REFERENCES wod (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wod_mouvement ADD CONSTRAINT FK_877F1FC8ECD1C222 FOREIGN KEY (mouvement_id) REFERENCES mouvement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC60BB6FE6');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC91C30030');
        $this->addSql('ALTER TABLE favori DROP FOREIGN KEY FK_EF85A2CCA76ED395');
        $this->addSql('ALTER TABLE favori DROP FOREIGN KEY FK_EF85A2CC91C30030');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY FK_DF7DFD0EFE6BCB8B');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY FK_DF7DFD0E91C30030');
        $this->addSql('ALTER TABLE wod DROP FOREIGN KEY FK_64575EE60BB6FE6');
        $this->addSql('ALTER TABLE wod_like DROP FOREIGN KEY FK_1A48F115A76ED395');
        $this->addSql('ALTER TABLE wod_like DROP FOREIGN KEY FK_1A48F11591C30030');
        $this->addSql('ALTER TABLE wod_mouvement DROP FOREIGN KEY FK_877F1FC891C30030');
        $this->addSql('ALTER TABLE wod_mouvement DROP FOREIGN KEY FK_877F1FC8ECD1C222');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE favori');
        $this->addSql('DROP TABLE mouvement');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE seance');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE wod');
        $this->addSql('DROP TABLE wod_like');
        $this->addSql('DROP TABLE wod_mouvement');
    }
}
