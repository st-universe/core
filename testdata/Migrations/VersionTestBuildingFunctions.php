<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestBuildingFunctions extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds building functions.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_buildings_functions (id, buildings_id, function)
                VALUES (87, 424242, 10);
        ');
    }
}
