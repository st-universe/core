<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestFaction extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a default faction.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_factions ("name",description,darker_color,chooseable,player_limit,start_building_id,start_research_id,start_map_id,close_combat_score,positive_effect_primary_commodity_id,positive_effect_secondary_commodity_id)
            VALUES (\'Vereinte Föderation der Planeten\',\'Die Föderation ist in STU die vielseitigste Fraktion. Sie hat mehr Schiffsrümpfe zur Auswahl als die anderen spielbaren Rassen. Der Schwerpunkt liegt auf der Forschung, aber Föderationssiedler können sich auch im Kampf behaupten.\',\'#0d2029\',true,50,82010100,1001,20645,100,1001,1601);'
        );
    }
}
