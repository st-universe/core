<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250614091852_NPCLOGFACTION extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds faction_id to npc_log';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_npc_log ADD faction_id INT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_npc_log DROP faction_id
        SQL);
    }
}
