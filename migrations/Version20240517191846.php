<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240517191846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_sessions ADD host_id VARCHAR(8) DEFAULT NULL');
        $this->addSql('ALTER TABLE game_sessions ADD CONSTRAINT FK_312462351FB8D185 FOREIGN KEY (host_id) REFERENCES players (player_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_312462351FB8D185 ON game_sessions (host_id)');
        $this->addSql('ALTER TABLE players ADD is_dead TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_sessions DROP FOREIGN KEY FK_312462351FB8D185');
        $this->addSql('DROP INDEX UNIQ_312462351FB8D185 ON game_sessions');
        $this->addSql('ALTER TABLE game_sessions DROP host_id');
        $this->addSql('ALTER TABLE players DROP is_dead');
    }
}
