<?php

declare(strict_types=1);

namespace Stu\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20241123194015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add NPC Buildable boolean';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER TABLE stu_rumps ADD npc_buildable BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_rumps DROP npc_buildable');
    }
}