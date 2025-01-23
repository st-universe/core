<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestModulesQueue extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a colony module queue.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_modules_queue (colony_id, module_id, count, buildingfunction)
                VALUES (42, 10101, 1, 10);
        ');
    }
}
