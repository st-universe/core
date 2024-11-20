<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestRumps extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_rumps.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_rumps (id, category_id, role_id, evade_chance, hit_chance, module_level, base_crew, base_eps, base_reactor, base_hull, base_shield, base_damage, base_sensor_range, base_torpedo_storage, phaser_volleys, phaser_hull_damage_factor, phaser_shield_damage_factor, torpedo_level, torpedo_volleys, name, is_buildable, is_npc, eps_cost, storage, slots, buildtime, sort, database_id, commodity_id, flight_ecost, beam_factor, special_slots, shuttle_slots, needed_workbees, tractor_mass, tractor_payload, prestige, base_warpdrive)
                VALUES (6501, 6, 5, 0, 75, 3, 1, 120, 60, 910, 910, 48, 3, 0, 2, 100, 150, 5, 0, \'Aerie\', true, false, 50, 155, 0, 9600, 6501, 6501001, NULL, 2, 20, 2, 5, NULL, 133000, 159600, -20, 60),
                       (8, 7, 1, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 0, 0, 0, 0, \'Wrack\', false, false, 0, 500, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 5, 6, 0, 0),
                       (101, 9, 1, 0, 0, 0, 0, 0, 0, 50, 0, 0, 0, 0, 0, 0, 0, 0, 0, \'Rettungskapsel\', false, false, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 3, 4, -20, 0);
        ');
    }
}
