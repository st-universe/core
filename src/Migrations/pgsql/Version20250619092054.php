<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250619092054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Optimized foreign key relations.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_deals ADD CONSTRAINT FK_6DAE42FC4448F8DA FOREIGN KEY (faction_id) REFERENCES stu_factions (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6DAE42FC4448F8DA ON stu_deals (faction_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX planet_type_idx RENAME TO IDX_5C1857F8506FDB14
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ALTER race DROP NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_deals DROP CONSTRAINT FK_6DAE42FC4448F8DA
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6DAE42FC4448F8DA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ALTER race SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_5c1857f8506fdb14 RENAME TO planet_type_idx
        SQL);
    }
}
