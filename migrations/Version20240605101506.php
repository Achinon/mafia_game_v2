<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240605101506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game_sessions (game_session_id VARCHAR(12) NOT NULL, host_id VARCHAR(8) DEFAULT NULL, join_code VARCHAR(6) NOT NULL, day_count INT NOT NULL, ms_time_scheduler_delay VARCHAR(255) NOT NULL, ms_time_created VARCHAR(255) NOT NULL, ms_time_last_updated VARCHAR(255) NOT NULL, ms_time_started VARCHAR(255) DEFAULT NULL, stage_id INT NOT NULL, UNIQUE INDEX UNIQ_312462351FB8D185 (host_id), PRIMARY KEY(game_session_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session_enabled_roles (game_session_id VARCHAR(12) NOT NULL, role_id INT NOT NULL, INDEX IDX_D393F4068FE32B32 (game_session_id), INDEX IDX_D393F406D60322AC (role_id), PRIMARY KEY(game_session_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hang (id INT AUTO_INCREMENT NOT NULL, player_id_to_hang VARCHAR(8) NOT NULL, player_id_voting VARCHAR(8) NOT NULL, ms_time VARCHAR(255) NOT NULL, INDEX IDX_BE6B1335D8BD2FB7 (player_id_to_hang), UNIQUE INDEX UNIQ_BE6B13357A135A81 (player_id_voting), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE players (player_id VARCHAR(8) NOT NULL, game_session_id VARCHAR(12) NOT NULL, role_id INT DEFAULT NULL, is_dead TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_264E43A68FE32B32 (game_session_id), INDEX IDX_264E43A6D60322AC (role_id), PRIMARY KEY(player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE roles (role_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B63E2EC75E237E06 (name), PRIMARY KEY(role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE votes (id INT AUTO_INCREMENT NOT NULL, player_id VARCHAR(8) NOT NULL, ms_time VARCHAR(255) NOT NULL, vote_type INT NOT NULL, INDEX IDX_518B7ACF99E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE game_sessions ADD CONSTRAINT FK_312462351FB8D185 FOREIGN KEY (host_id) REFERENCES players (player_id)');
        $this->addSql('ALTER TABLE session_enabled_roles ADD CONSTRAINT FK_D393F4068FE32B32 FOREIGN KEY (game_session_id) REFERENCES game_sessions (game_session_id)');
        $this->addSql('ALTER TABLE session_enabled_roles ADD CONSTRAINT FK_D393F406D60322AC FOREIGN KEY (role_id) REFERENCES roles (role_id)');
        $this->addSql('ALTER TABLE hang ADD CONSTRAINT FK_BE6B1335D8BD2FB7 FOREIGN KEY (player_id_to_hang) REFERENCES players (player_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hang ADD CONSTRAINT FK_BE6B13357A135A81 FOREIGN KEY (player_id_voting) REFERENCES players (player_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE players ADD CONSTRAINT FK_264E43A68FE32B32 FOREIGN KEY (game_session_id) REFERENCES game_sessions (game_session_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE players ADD CONSTRAINT FK_264E43A6D60322AC FOREIGN KEY (role_id) REFERENCES roles (role_id)');
        $this->addSql('ALTER TABLE votes ADD CONSTRAINT FK_518B7ACF99E6F5DF FOREIGN KEY (player_id) REFERENCES players (player_id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_sessions DROP FOREIGN KEY FK_312462351FB8D185');
        $this->addSql('ALTER TABLE session_enabled_roles DROP FOREIGN KEY FK_D393F4068FE32B32');
        $this->addSql('ALTER TABLE session_enabled_roles DROP FOREIGN KEY FK_D393F406D60322AC');
        $this->addSql('ALTER TABLE hang DROP FOREIGN KEY FK_BE6B1335D8BD2FB7');
        $this->addSql('ALTER TABLE hang DROP FOREIGN KEY FK_BE6B13357A135A81');
        $this->addSql('ALTER TABLE players DROP FOREIGN KEY FK_264E43A68FE32B32');
        $this->addSql('ALTER TABLE players DROP FOREIGN KEY FK_264E43A6D60322AC');
        $this->addSql('ALTER TABLE votes DROP FOREIGN KEY FK_518B7ACF99E6F5DF');
        $this->addSql('DROP TABLE game_sessions');
        $this->addSql('DROP TABLE session_enabled_roles');
        $this->addSql('DROP TABLE hang');
        $this->addSql('DROP TABLE players');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE votes');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
