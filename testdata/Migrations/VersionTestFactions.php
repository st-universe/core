<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestFactions extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_factions.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_factions (id, name, description, darker_color, chooseable, player_limit, start_building_id, start_research_id, start_map_id, close_combat_score, positive_effect_primary_commodity_id, positive_effect_secondary_commodity_id) VALUES (9, \'Fraktionslos\', \'nö\', \'#006400\', false, 0, 0, NULL, 15007, NULL, NULL, NULL);
INSERT INTO stu_factions (id, name, description, darker_color, chooseable, player_limit, start_building_id, start_research_id, start_map_id, close_combat_score, positive_effect_primary_commodity_id, positive_effect_secondary_commodity_id) VALUES (6, \'Pakled\', \'Sucher...\', \'#5c2e00\', false, 0, 0, NULL, 15007, 100, NULL, NULL);
INSERT INTO stu_factions (id, name, description, darker_color, chooseable, player_limit, start_building_id, start_research_id, start_map_id, close_combat_score, positive_effect_primary_commodity_id, positive_effect_secondary_commodity_id) VALUES (8, \'Borg-Kollektiv\', \'Alkoven\', \'#006400\', false, 0, 0, NULL, 15007, 200, NULL, NULL);
INSERT INTO stu_factions (id, name, description, darker_color, chooseable, player_limit, start_building_id, start_research_id, start_map_id, close_combat_score, positive_effect_primary_commodity_id, positive_effect_secondary_commodity_id) VALUES (1, \'Vereinte Föderation der Planeten\', \'Die Föderation ist in STU die vielseitigste Fraktion.
INSERT INTO stu_factions (id, name, description, darker_color, chooseable, player_limit, start_building_id, start_research_id, start_map_id, close_combat_score, positive_effect_primary_commodity_id, positive_effect_secondary_commodity_id) VALUES (2, \'Romulanisches Imperium\', \'Romulaner gelten allgemein als hinterhältig und verschlagen - ein Ruf, den sie auch in der Cragganmore Verwerfung eifrig pflegen.
INSERT INTO stu_factions (id, name, description, darker_color, chooseable, player_limit, start_building_id, start_research_id, start_map_id, close_combat_score, positive_effect_primary_commodity_id, positive_effect_secondary_commodity_id) VALUES (7, \'Kazon\', \'Wasser, ich will Wasser\', \'#8B5A2B\', false, 0, 0, NULL, 15007, 200, NULL, NULL);
INSERT INTO stu_factions (id, name, description, darker_color, chooseable, player_limit, start_building_id, start_research_id, start_map_id, close_combat_score, positive_effect_primary_commodity_id, positive_effect_secondary_commodity_id) VALUES (3, \'Klingonisches Imperium\', \'Die Klingonen sind ein kriegerisches Volk aus dem Betaquadranten.
INSERT INTO stu_factions (id, name, description, darker_color, chooseable, player_limit, start_building_id, start_research_id, start_map_id, close_combat_score, positive_effect_primary_commodity_id, positive_effect_secondary_commodity_id) VALUES (4, \'Cardassianische Union\', \'Schon wieder eine Station weniger :-(\', \'#402e00\', true, 50, 82010400, 1002, 15007, 145, 1004, 1604);
INSERT INTO stu_factions (id, name, description, darker_color, chooseable, player_limit, start_building_id, start_research_id, start_map_id, close_combat_score, positive_effect_primary_commodity_id, positive_effect_secondary_commodity_id) VALUES (5, \'Ferengi Allianz\', \'Die Ferengi sind eine Kultur skrupelloser Kapitalisten, die selbst ihre eigene Großmutter verkaufen würden, wenn sie Latinum dafür bekämen.
        ');
    }
}
