<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestRump extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a default rump.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_rumps (category_id,role_id,evade_chance,hit_chance,module_level,base_crew,base_eps,base_reactor,base_hull,base_shield,base_damage,base_sensor_range,base_torpedo_storage,phaser_volleys,phaser_hull_damage_factor,phaser_shield_damage_factor,torpedo_level,torpedo_volleys,"name",is_buildable,is_npc,eps_cost,"storage",slots,buildtime,sort,database_id,commodity_id,flight_ecost,beam_factor,special_slots,shuttle_slots,needed_workbees,tractor_mass,tractor_payload,prestige,base_warpdrive) VALUES (3,2,0,75,3,1,190,95,910,910,80,3,35,3,100,225,3,2,\'B\'\'Rel\',true,false,50,70,0,9600,3203,3203001,NULL,3,15,2,0,NULL,342000,410400,9,63);'
        );
    }
}
