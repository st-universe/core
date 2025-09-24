<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250924083731_NPCModule extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds is_npc column to module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_modules ADD is_npc BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_modules DROP is_npc');
    }
}