<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestRumps extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_rump.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_rump (id, category_id, role_id, base_torpedo_storage, phaser_volleys, phaser_hull_damage_factor, phaser_shield_damage_factor, torpedo_level, torpedo_volleys, name, is_buildable, is_npc, eps_cost, storage, slots, buildtime, sort, database_id, commodity_id, flight_ecost, beam_factor, shuttle_slots, needed_workbees, tractor_mass, tractor_payload, prestige)
                VALUES (6501, 6, 5, 0, 2, 100, 150, 5, 0, \'Aerie\', 1, 0, 50, 155, 0, 9600, 6501, 6501001, NULL, 2, 20, 5, NULL, 133000, 159600, -20),
                    (8, 7, 1, 0, 0, 0, 0, 0, 0, \'Wrack\', 0, 0, 0, 500, 0, 0, 0, NULL, NULL, 0, 1, 0, NULL, 5, 6, 0),
                    (101, 9, 1, 0, 0, 0, 0, 0, 0, \'Rettungskapsel\', 0, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, NULL, 3, 4, -20),
                    (10053, 12,15,500,6,100,200,4,6,\'Außenposten\',0,0,0,10000,30,40,10053,6810053,NULL,0,25,15,30,34500000,41400000,0);
        ');
    }
}
