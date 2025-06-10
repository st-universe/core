<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250610104115_LssMode extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Transfer lss mode to lss system data.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE stu_spacecraft_system ss
            SET data = '{"sensorRange":' || (ss.data::json->>'sensorRange') || ',"mode":' || (select sp.lss_mode from stu_spacecraft sp where sp.id = ss.spacecraft_id) || '}'
            WHERE system_type = 8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft DROP lss_mode
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ADD lss_mode SMALLINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE stu_spacecraft sp
            SET lss_mode = (SELECT (ss.data::json->>'mode')::smallint FROM stu_spacecraft_system ss WHERE ss.system_type = 8 AND ss.spacecraft_id = sp.id)
            WHERE EXISTS (SELECT * FROM stu_spacecraft_system ss WHERE ss.system_type = 8 AND ss.spacecraft_id = sp.id)
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE stu_spacecraft SET lss_mode = 1 WHERE lss_mode IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ALTER lss_mode SET NOT NULL
        SQL);
    }
}
