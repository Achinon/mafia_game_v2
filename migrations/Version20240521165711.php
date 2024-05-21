<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240521165711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE players ADD role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE players ADD CONSTRAINT FK_264E43A6D60322AC FOREIGN KEY (role_id) REFERENCES roles (role_id)');
        $this->addSql('CREATE INDEX IDX_264E43A6D60322AC ON players (role_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE players DROP FOREIGN KEY FK_264E43A6D60322AC');
        $this->addSql('DROP INDEX IDX_264E43A6D60322AC ON players');
        $this->addSql('ALTER TABLE players DROP role_id');
    }
}
