<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250602200524_NewPmCountGameTurnStats extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added game turn stats for new pm count during last tick interval.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_game_turn_stats ADD new_pm_count INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX pm_date_idx ON stu_pms (date)
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE stu_game_turn_stats stats
            SET new_pm_count = (
                SELECT COUNT(*)
                FROM stu_pms p
                WHERE p.date > (
                    SELECT t.startdate
                    FROM stu_game_turns t
                    WHERE t.id = (stats.turn_id - 1)
                )
                AND p.date < (
                    SELECT t.enddate
                    FROM stu_game_turns t
                    WHERE t.id = (stats.turn_id - 1)
                )
            )
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_game_turn_stats ALTER new_pm_count SET NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX pm_date_idx
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_game_turn_stats DROP new_pm_count
        SQL);
    }
}
