<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestFaction extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default factions.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_factions (id, name, description, darker_color, chooseable, player_limit, start_building_id, start_research_id, start_map_id, close_combat_score, positive_effect_primary_commodity_id, positive_effect_secondary_commodity_id)
            VALUES  (9, \'Fraktionslos\', \'nö\', \'#006400\', 0, 0, 0, NULL, 15007, NULL, NULL, NULL),
                    (6, \'Pakled\', \'Sucher...\', \'#5c2e00\', 0, 0, 0, NULL, 15007, 100, NULL, NULL),
                    (8, \'Borg-Kollektiv\', \'Alkoven\', \'#006400\', 0, 0, 0, NULL, 15007, 200, NULL, NULL),
                    (1, \'Vereinte Föderation der Planeten\', \'Die Föderation ist in STU die vielseitigste Fraktion. 
                        Sie hat mehr Schiffsrümpfe zur Auswahl als die anderen spielbaren Rassen.
                        Der Schwerpunkt liegt auf der Forschung, aber Föderationssiedler können sich auch im Kampf behaupten.\', \'#0d2029\', 1, 50, 82010100, 1001, 15007, 100, 1001, 1601),
                    (2, \'Romulanisches Imperium\', \'Romulaner gelten allgemein als hinterhältig und verschlagen - ein Ruf, den sie auch in der Cragganmore Verwerfung eifrig pflegen.
                        Wer dieses Volk auswählt kann Tarnvorrichtungen in seine Schiffe einbauen.
                        Darüber hinaus sind die Romulaner die einzige Rasse, die die hochwertigsten Torpedowerfer benutzen kann.\', \'#0d2000\', 1, 50, 82010200, 1002, 15007, 127, 1002, 1602),
                    (7, \'Kazon\', \'Wasser, ich will Wasser\', \'#8B5A2B\', 0, 0, 0, NULL, 15007, 200, NULL, NULL),
                    (3, \'Klingonisches Imperium\', \'Die Klingonen sind ein kriegerisches Volk aus dem Betaquadranten.
                        Wer dieses Volk spielt, verfügt über eine Auswahl an effektiven Kampfschiffen und kann Tarnvorrichtungen benutzen.\', \'#2f0300\', 1, 50, 82010300, 1002, 15007, 182, 1003, 1603),
                    (4, \'Cardassianische Union\', \'Schon wieder eine Station weniger :-(\', \'#402e00\', 1, 50, 82010400, 1002, 15007, 145, 1004, 1604),
                    (5, \'Ferengi Allianz\', \'Die Ferengi sind eine Kultur skrupelloser Kapitalisten, die selbst ihre eigene Großmutter verkaufen würden, wenn sie Latinum dafür bekämen.
                        In STU haben sie Schiffe mit hoher Ladekapazität und können Tachyonscanner einsetzen, um getarnte Schiffe aufzuspüren.\', \'#402100\', 1, 50, 82010500, 1002, 15007, 73, 1005, 1605);
            '
        );
    }
}
