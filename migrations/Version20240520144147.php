<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240520144147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE players DROP FOREIGN KEY FK_264E43A68FE32B32');
        $this->addSql('ALTER TABLE players CHANGE game_session_id game_session_id VARCHAR(12) NOT NULL');
        $this->addSql('ALTER TABLE players ADD CONSTRAINT FK_264E43A68FE32B32 FOREIGN KEY (game_session_id) REFERENCES game_sessions (game_session_id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE players DROP FOREIGN KEY FK_264E43A68FE32B32');
        $this->addSql('ALTER TABLE players CHANGE game_session_id game_session_id VARCHAR(12) DEFAULT NULL');
        $this->addSql('ALTER TABLE players ADD CONSTRAINT FK_264E43A68FE32B32 FOREIGN KEY (game_session_id) REFERENCES game_sessions (game_session_id)');
    }
}
