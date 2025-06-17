<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250604052411_ExtractComputerSystemData extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extracted computer specific variables from spacecraft entity to system data.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE stu_spacecraft
            SET direction = 0
            WHERE direction IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE stu_spacecraft_system ss
            SET DATA = (
                SELECT '{"hitChance":' || s.hit_chance || ',"evadeChance":' || s.evade_chance || ',"isInEmergency":' || s.in_emergency || ',"flightDirection":' || s.direction || ',"alertState":' || s.alvl || '}'
                FROM stu_spacecraft s
                WHERE s.id = ss.spacecraft_id
            )
            WHERE ss.system_type = 4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft DROP direction
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft DROP alvl
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft DROP hit_chance
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft DROP evade_chance
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft DROP in_emergency
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ADD direction SMALLINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ADD alvl SMALLINT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ADD hit_chance SMALLINT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ADD evade_chance SMALLINT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ADD in_emergency BOOLEAN NOT NULL
        SQL);
    }
}
