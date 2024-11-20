<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestShipSystem extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_ship_system.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_ship_system (id, ship_id, system_type, module_id, status, mode, cooldown, data)
                VALUES (14, 3, 16, NULL, 100, 1, NULL, NULL),
                       (15, 3, 14, NULL, 100, 1, NULL, NULL),
                       (16, 3, 13, NULL, 100, 3, NULL, NULL),
                       (17, 3, 11, 10202, 100, 1, NULL, NULL),
                       (19, 3, 2, 10403, 100, 1, NULL, NULL),
                       (20, 3, 3, 10502, 100, 1, NULL, \'{"output":54,"load":810}\'),
                       (21, 3, 4, 11902, 100, 1, NULL, NULL),
                       (22, 3, 5, 10702, 100, 1, NULL, NULL),
                       (24, 3, 9, NULL, 100, 1, NULL, NULL),
                       (25, 3, 10, 10912, 100, 1, NULL, \'{"wd":57,"maxwd":58,"split":100,"autoCarryOver":false}\'),
                       (18, 3, 1, 10302, 100, 1, NULL, \'{"eps":107,"maxEps":108,"maxBattery":36,"battery":36,"batteryCooldown":0,"reloadBattery":false}\'),
                       (23, 3, 8, 10602, 100, 2, NULL, NULL);
        ');
    }
}
