<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250610094046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixed primary key field order.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user_map DROP CONSTRAINT stu_user_map_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user_map ADD PRIMARY KEY (cx, cy, user_id, layer_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX stu_user_map_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user_map ADD PRIMARY KEY (user_id, layer_id, cx, cy)
        SQL);
    }
}
