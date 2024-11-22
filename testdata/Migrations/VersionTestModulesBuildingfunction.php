<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestModulesBuildingfunction extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a module building function.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_modules_buildingfunction (id, module_id,buildingfunction) VALUES (1, 10101,10);');
    }
}
