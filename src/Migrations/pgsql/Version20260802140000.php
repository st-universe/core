<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260802140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allows spacecraft with a torpedo transport module to retain multiple loaded torpedo types and select the fireable type.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_823719111C6AF6FD');
        $this->addSql('CREATE UNIQUE INDEX torpedo_storage_type_unique_idx ON stu_torpedo_storage (spacecraft_id, torpedo_type)');
        $this->addSql('ALTER TABLE stu_torpedo_storage ADD is_active BOOLEAN DEFAULT FALSE NOT NULL');
        $this->addSql('UPDATE stu_torpedo_storage ts SET is_active = TRUE FROM stu_spacecraft s, stu_rump r, stu_torpedo_types tt WHERE ts.spacecraft_id = s.id AND s.rump_id = r.id AND ts.torpedo_type = tt.id AND tt.level = r.torpedo_level');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_torpedo_storage DROP is_active');
        $this->addSql('DROP INDEX torpedo_storage_type_unique_idx');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_823719111C6AF6FD ON stu_torpedo_storage (spacecraft_id)');
    }
}
