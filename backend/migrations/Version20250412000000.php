<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250412000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial database schema for football league';
    }

    public function up(Schema $schema): void
    {
        // Teams table
        $this->addSql(
            'CREATE TABLE teams (
        id INT AUTO_INCREMENT NOT NULL,
        name VARCHAR(255) NOT NULL,
        strength_value INT NOT NULL,
        statistics_played INT NOT NULL DEFAULT 0,
        statistics_won INT NOT NULL DEFAULT 0,
        statistics_drawn INT NOT NULL DEFAULT 0,
        statistics_lost INT NOT NULL DEFAULT 0,
        statistics_goals_for INT NOT NULL DEFAULT 0,
        statistics_goals_against INT NOT NULL DEFAULT 0,
        PRIMARY KEY(id)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );

        // League Tables table (move this up!)
        $this->addSql(
            'CREATE TABLE league_tables (
        id INT AUTO_INCREMENT NOT NULL,
        current_week INT NOT NULL DEFAULT 0,
        is_completed TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY(id)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );

        // Games table
        $this->addSql(
            'CREATE TABLE games (
        id INT AUTO_INCREMENT NOT NULL,
        home_team_id INT NOT NULL,
        away_team_id INT NOT NULL,
        week INT NOT NULL,
        result_home_goals INT DEFAULT NULL,
        result_away_goals INT DEFAULT NULL,
        league_table_id INT NOT NULL,
        PRIMARY KEY(id),
        INDEX IDX_games_home_team_id (home_team_id),
        INDEX IDX_games_away_team_id (away_team_id),
        INDEX IDX_games_league_table_id (league_table_id),
        CONSTRAINT FK_games_home_team_id FOREIGN KEY (home_team_id) REFERENCES teams (id) ON DELETE CASCADE,
        CONSTRAINT FK_games_away_team_id FOREIGN KEY (away_team_id) REFERENCES teams (id) ON DELETE CASCADE,
        CONSTRAINT FK_games_league_table_id FOREIGN KEY (league_table_id) REFERENCES league_tables (id) ON DELETE CASCADE
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );

        // League Teams (join table)
        $this->addSql(
            'CREATE TABLE league_teams (
        league_table_id INT NOT NULL,
        team_id INT NOT NULL,
        PRIMARY KEY(league_table_id, team_id),
        INDEX IDX_league_teams_league_table_id (league_table_id),
        INDEX IDX_league_teams_team_id (team_id),
        CONSTRAINT FK_league_teams_league_table_id FOREIGN KEY (league_table_id) REFERENCES league_tables (id) ON DELETE CASCADE,
        CONSTRAINT FK_league_teams_team_id FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE CASCADE
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE league_teams');
        $this->addSql('DROP TABLE games');
        $this->addSql('DROP TABLE league_tables');
        $this->addSql('DROP TABLE teams');
    }
}