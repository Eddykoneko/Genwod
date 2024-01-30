<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240130133945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire ADD user_id_id INT NOT NULL, ADD exercice_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC26C958BE FOREIGN KEY (exercice_id_id) REFERENCES exercice (id)');
        $this->addSql('CREATE INDEX IDX_67F068BC9D86650F ON commentaire (user_id_id)');
        $this->addSql('CREATE INDEX IDX_67F068BC26C958BE ON commentaire (exercice_id_id)');
        $this->addSql('ALTER TABLE favoris ADD user_id_id INT NOT NULL, ADD exercice_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE favoris ADD CONSTRAINT FK_8933C4329D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE favoris ADD CONSTRAINT FK_8933C43226C958BE FOREIGN KEY (exercice_id_id) REFERENCES exercice (id)');
        $this->addSql('CREATE INDEX IDX_8933C4329D86650F ON favoris (user_id_id)');
        $this->addSql('CREATE INDEX IDX_8933C43226C958BE ON favoris (exercice_id_id)');
        $this->addSql('ALTER TABLE historique_exercice ADD user_id_id INT NOT NULL, ADD exercice_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE historique_exercice ADD CONSTRAINT FK_24C0BFBF9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE historique_exercice ADD CONSTRAINT FK_24C0BFBF26C958BE FOREIGN KEY (exercice_id_id) REFERENCES exercice (id)');
        $this->addSql('CREATE INDEX IDX_24C0BFBF9D86650F ON historique_exercice (user_id_id)');
        $this->addSql('CREATE INDEX IDX_24C0BFBF26C958BE ON historique_exercice (exercice_id_id)');
        $this->addSql('ALTER TABLE leaderboard ADD user_id_id INT NOT NULL, ADD exercice_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE leaderboard ADD CONSTRAINT FK_182E52539D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE leaderboard ADD CONSTRAINT FK_182E525326C958BE FOREIGN KEY (exercice_id_id) REFERENCES exercice (id)');
        $this->addSql('CREATE INDEX IDX_182E52539D86650F ON leaderboard (user_id_id)');
        $this->addSql('CREATE INDEX IDX_182E525326C958BE ON leaderboard (exercice_id_id)');
        $this->addSql('ALTER TABLE like_dislike ADD user_id_id INT NOT NULL, ADD exercice_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE like_dislike ADD CONSTRAINT FK_ADB6A6899D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE like_dislike ADD CONSTRAINT FK_ADB6A68926C958BE FOREIGN KEY (exercice_id_id) REFERENCES exercice (id)');
        $this->addSql('CREATE INDEX IDX_ADB6A6899D86650F ON like_dislike (user_id_id)');
        $this->addSql('CREATE INDEX IDX_ADB6A68926C958BE ON like_dislike (exercice_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC9D86650F');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC26C958BE');
        $this->addSql('DROP INDEX IDX_67F068BC9D86650F ON commentaire');
        $this->addSql('DROP INDEX IDX_67F068BC26C958BE ON commentaire');
        $this->addSql('ALTER TABLE commentaire DROP user_id_id, DROP exercice_id_id');
        $this->addSql('ALTER TABLE favoris DROP FOREIGN KEY FK_8933C4329D86650F');
        $this->addSql('ALTER TABLE favoris DROP FOREIGN KEY FK_8933C43226C958BE');
        $this->addSql('DROP INDEX IDX_8933C4329D86650F ON favoris');
        $this->addSql('DROP INDEX IDX_8933C43226C958BE ON favoris');
        $this->addSql('ALTER TABLE favoris DROP user_id_id, DROP exercice_id_id');
        $this->addSql('ALTER TABLE historique_exercice DROP FOREIGN KEY FK_24C0BFBF9D86650F');
        $this->addSql('ALTER TABLE historique_exercice DROP FOREIGN KEY FK_24C0BFBF26C958BE');
        $this->addSql('DROP INDEX IDX_24C0BFBF9D86650F ON historique_exercice');
        $this->addSql('DROP INDEX IDX_24C0BFBF26C958BE ON historique_exercice');
        $this->addSql('ALTER TABLE historique_exercice DROP user_id_id, DROP exercice_id_id');
        $this->addSql('ALTER TABLE leaderboard DROP FOREIGN KEY FK_182E52539D86650F');
        $this->addSql('ALTER TABLE leaderboard DROP FOREIGN KEY FK_182E525326C958BE');
        $this->addSql('DROP INDEX IDX_182E52539D86650F ON leaderboard');
        $this->addSql('DROP INDEX IDX_182E525326C958BE ON leaderboard');
        $this->addSql('ALTER TABLE leaderboard DROP user_id_id, DROP exercice_id_id');
        $this->addSql('ALTER TABLE like_dislike DROP FOREIGN KEY FK_ADB6A6899D86650F');
        $this->addSql('ALTER TABLE like_dislike DROP FOREIGN KEY FK_ADB6A68926C958BE');
        $this->addSql('DROP INDEX IDX_ADB6A6899D86650F ON like_dislike');
        $this->addSql('DROP INDEX IDX_ADB6A68926C958BE ON like_dislike');
        $this->addSql('ALTER TABLE like_dislike DROP user_id_id, DROP exercice_id_id');
    }
}
