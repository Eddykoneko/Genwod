<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240229143203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, exercice_id_id INT NOT NULL, commentaire LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_67F068BC9D86650F (user_id_id), INDEX IDX_67F068BC26C958BE (exercice_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exercice (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(80) NOT NULL, description LONGTEXT DEFAULT NULL, duree INT DEFAULT NULL, difficulte VARCHAR(255) NOT NULL, score INT DEFAULT NULL, mode VARCHAR(60) NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE favoris (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, exercice_id_id INT NOT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_8933C4329D86650F (user_id_id), INDEX IDX_8933C43226C958BE (exercice_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE historique_exercice (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, exercice_id_id INT NOT NULL, mode VARCHAR(60) NOT NULL, nombre_tours INT DEFAULT NULL, nombre_repetition INT NOT NULL, temps DATETIME NOT NULL, commentaire LONGTEXT DEFAULT NULL, INDEX IDX_24C0BFBF9D86650F (user_id_id), INDEX IDX_24C0BFBF26C958BE (exercice_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE leaderboard (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, exercice_id_id INT NOT NULL, INDEX IDX_182E52539D86650F (user_id_id), INDEX IDX_182E525326C958BE (exercice_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE like_dislike (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, exercice_id_id INT NOT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_ADB6A6899D86650F (user_id_id), INDEX IDX_ADB6A68926C958BE (exercice_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', age INT NOT NULL, poids INT NOT NULL, taille INT NOT NULL, genre VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC26C958BE FOREIGN KEY (exercice_id_id) REFERENCES exercice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favoris ADD CONSTRAINT FK_8933C4329D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE favoris ADD CONSTRAINT FK_8933C43226C958BE FOREIGN KEY (exercice_id_id) REFERENCES exercice (id)');
        $this->addSql('ALTER TABLE historique_exercice ADD CONSTRAINT FK_24C0BFBF9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE historique_exercice ADD CONSTRAINT FK_24C0BFBF26C958BE FOREIGN KEY (exercice_id_id) REFERENCES exercice (id)');
        $this->addSql('ALTER TABLE leaderboard ADD CONSTRAINT FK_182E52539D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE leaderboard ADD CONSTRAINT FK_182E525326C958BE FOREIGN KEY (exercice_id_id) REFERENCES exercice (id)');
        $this->addSql('ALTER TABLE like_dislike ADD CONSTRAINT FK_ADB6A6899D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE like_dislike ADD CONSTRAINT FK_ADB6A68926C958BE FOREIGN KEY (exercice_id_id) REFERENCES exercice (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC9D86650F');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC26C958BE');
        $this->addSql('ALTER TABLE favoris DROP FOREIGN KEY FK_8933C4329D86650F');
        $this->addSql('ALTER TABLE favoris DROP FOREIGN KEY FK_8933C43226C958BE');
        $this->addSql('ALTER TABLE historique_exercice DROP FOREIGN KEY FK_24C0BFBF9D86650F');
        $this->addSql('ALTER TABLE historique_exercice DROP FOREIGN KEY FK_24C0BFBF26C958BE');
        $this->addSql('ALTER TABLE leaderboard DROP FOREIGN KEY FK_182E52539D86650F');
        $this->addSql('ALTER TABLE leaderboard DROP FOREIGN KEY FK_182E525326C958BE');
        $this->addSql('ALTER TABLE like_dislike DROP FOREIGN KEY FK_ADB6A6899D86650F');
        $this->addSql('ALTER TABLE like_dislike DROP FOREIGN KEY FK_ADB6A68926C958BE');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE exercice');
        $this->addSql('DROP TABLE favoris');
        $this->addSql('DROP TABLE historique_exercice');
        $this->addSql('DROP TABLE leaderboard');
        $this->addSql('DROP TABLE like_dislike');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
