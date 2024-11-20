<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestShips extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_ships.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_ships (id, user_id, rumps_id, plans_id, fleets_id, direction, name, alvl, huelle, max_huelle, schilde, max_schilde, dock, former_rumps_id, database_id, is_destroyed, disabled, hit_chance, evade_chance, base_damage, sensor_range, shield_regeneration_timer, state, is_fleet_leader, influence_area_id, tractored_ship_id, lss_mode, holding_web_id, type, in_emergency, location_id)
            VALUES (3, 102, 6501, 2324, NULL, 0, \'Aerie\', 1, 819, 819, 819, 819, NULL, 0, NULL, false, false, 68, 0, 43, 2, 0, 0, false, NULL, NULL, 1, NULL, 0, false, 15247);
        ');
    }
}
