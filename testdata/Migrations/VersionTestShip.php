<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestShip extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_ship.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_ship (id, fleet_id, docked_to_id, is_fleet_leader)
            VALUES (42, NULL, 43, 0),
                    (77, 77, NULL, 1),
                    (78, NULL, NULL, 0);
        ');
    }
}
