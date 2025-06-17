<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250603172257_MoveBaseDamage extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Moves spacecraft base_damage to energy weapon system data.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE stu_spacecraft_system ss
            SET DATA = (
                SELECT '{"baseDamage":' || s.base_damage || '}'
                FROM stu_spacecraft s
                WHERE s.id = ss.spacecraft_id
            )
            WHERE ss.system_type = 5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft DROP base_damage
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ADD base_damage SMALLINT NOT NULL
        SQL);
    }
}
