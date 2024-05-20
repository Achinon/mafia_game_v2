<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240517155530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game_sessions (game_session_id VARCHAR(12) NOT NULL, join_code VARCHAR(6) NOT NULL, day_count INT NOT NULL, is_night TINYINT(1) NOT NULL, ms_time_created VARCHAR(255) NOT NULL, ms_time_started VARCHAR(255) NOT NULL, stage_id INT NOT NULL, PRIMARY KEY(game_session_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session_enabled_roles (game_session_id VARCHAR(12) NOT NULL, role_id INT NOT NULL, INDEX IDX_D393F4068FE32B32 (game_session_id), INDEX IDX_D393F406D60322AC (role_id), PRIMARY KEY(game_session_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE players (player_id VARCHAR(8) NOT NULL, game_session_id VARCHAR(12) DEFAULT NULL, INDEX IDX_264E43A68FE32B32 (game_session_id), PRIMARY KEY(player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE roles (role_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE session_enabled_roles ADD CONSTRAINT FK_D393F4068FE32B32 FOREIGN KEY (game_session_id) REFERENCES game_sessions (game_session_id)');
        $this->addSql('ALTER TABLE session_enabled_roles ADD CONSTRAINT FK_D393F406D60322AC FOREIGN KEY (role_id) REFERENCES roles (role_id)');
        $this->addSql('ALTER TABLE players ADD CONSTRAINT FK_264E43A68FE32B32 FOREIGN KEY (game_session_id) REFERENCES game_sessions (game_session_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session_enabled_roles DROP FOREIGN KEY FK_D393F4068FE32B32');
        $this->addSql('ALTER TABLE session_enabled_roles DROP FOREIGN KEY FK_D393F406D60322AC');
        $this->addSql('ALTER TABLE players DROP FOREIGN KEY FK_264E43A68FE32B32');
        $this->addSql('DROP TABLE game_sessions');
        $this->addSql('DROP TABLE session_enabled_roles');
        $this->addSql('DROP TABLE players');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
