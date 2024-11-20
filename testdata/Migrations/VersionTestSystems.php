<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestSystems extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_systems.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_systems (id, type, name, max_x, max_y, bonus_fields, database_id, is_wormhole)
            VALUES (252, 1060, \'Stempor\'\'Arr\', 22, 22, 2, 6704252, false);
        ');
    }
}
