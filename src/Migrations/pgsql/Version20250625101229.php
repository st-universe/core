<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250625101229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_d1c60f739106126 RENAME TO IDX_174814E19106126
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_d1c60f73496dde10 RENAME TO UNIQ_174814E1496DDE10
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_d1c60f73a76ed395 RENAME TO IDX_174814E1A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_d1c60f73f0aa09db RENAME TO UNIQ_174814E1F0AA09DB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX dockingrights_station_idx RENAME TO IDX_E7D4B2A21BDB235
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump_base_values ALTER special_slots TYPE SMALLINT
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_e7d4b2a21bdb235 RENAME TO dockingrights_station_idx
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_174814e1f0aa09db RENAME TO uniq_d1c60f73f0aa09db
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_174814e1496dde10 RENAME TO uniq_d1c60f73496dde10
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_174814e1a76ed395 RENAME TO idx_d1c60f73a76ed395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_174814e19106126 RENAME TO idx_d1c60f739106126
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump_base_values ALTER special_slots TYPE INT
        SQL);
    }
}
