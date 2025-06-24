<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestRumpBaseValues extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_rump_base_values.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_rump_base_values (rump_id, evade_chance, hit_chance, module_level, base_crew, base_eps, base_reactor, base_hull, base_shield, base_damage, base_sensor_range, base_warpdrive, special_slots)
                VALUES (6501, 0, 75, 3, 1, 120, 60, 910, 910, 48, 3, 60, 2),
                       (8, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0),
                       (101, 0, 0, 0, 0, 0, 0, 50, 0, 0, 0, 0, 0),
                       (10053,0,70,5,0,500,175,21000,35000,200,4,0,3);
        ');
    }
}
