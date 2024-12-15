<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestSpacecraft extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_spacecraft.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_spacecraft (id, user_id, rump_id, plan_id, direction, name, alvl, huelle, max_huelle, schilde, max_schilde, database_id, disabled, hit_chance, evade_chance, base_damage, sensor_range, shield_regeneration_timer, state, tractored_ship_id, lss_mode, holding_web_id, type, in_emergency, location_id)
            VALUES (42, 101, 6501, 2324, NULL, \'Aerie\', 1, 819, 819, 819, 819, NULL, 0, 68, 0, 43, 2, 0, 0, NULL, 1, NULL, \'SHIP\', 0, 15247),
                    (77, 101, 6501, 2324, NULL, \'Aerie Zwo\', 1, 819, 819, 819, 819, NULL, 0, 68, 0, 43, 2, 0, 0, NULL, 1, NULL, \'SHIP\', 0, 204359),
                    (78, 101, 6501, 2324, NULL, \'Aerie Three\', 1, 819, 819, 819, 819, NULL, 0, 68, 0, 43, 2, 0, 0, NULL, 1, NULL, \'SHIP\', 0, 204359),
                    (43, 101, 10053, 689, NULL, \'Mighty AP\', 1, 20000, 21000, 24000, 25000, NULL, 0, 68, 0, 43, 2, 0, 0, NULL, 1, NULL, \'STATION\', 0, 15247);
        ');
    }
}
