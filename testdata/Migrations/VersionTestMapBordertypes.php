<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestMapBordertypes extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_map_bordertypes.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_map_bordertypes (id, faction_id, color, description) VALUES (1, 1, \'#000099\', \'Außengebiet (Föderation)\');
INSERT INTO stu_map_bordertypes (id, faction_id, color, description) VALUES (2, 1, \'#0000ff\', \'Kerngebiet (Föderation)\');
INSERT INTO stu_map_bordertypes (id, faction_id, color, description) VALUES (3, 2, \'#009900\', \'Romulanisches Imperium (Außengebiet)\');
INSERT INTO stu_map_bordertypes (id, faction_id, color, description) VALUES (4, 2, \'#00ff00\', \'Romulanisches Imperium (Kerngebiet)\');
INSERT INTO stu_map_bordertypes (id, faction_id, color, description) VALUES (5, 3, \'#990000\', \'Klingonisches Imperium (Außengebiet)\');
INSERT INTO stu_map_bordertypes (id, faction_id, color, description) VALUES (6, 3, \'#ff0000\', \'Klingonisches Imperium (Kerngebiet)\');
INSERT INTO stu_map_bordertypes (id, faction_id, color, description) VALUES (7, 4, \'#999900\', \'Cardassianische Union (Außengebiet)\');
INSERT INTO stu_map_bordertypes (id, faction_id, color, description) VALUES (8, 4, \'#ffff00\', \'Cardassianische Union (Kerngebiet)\');
INSERT INTO stu_map_bordertypes (id, faction_id, color, description) VALUES (9, 5, \'#DF7401\', \'Ferengi Allianz (Außengebiet)\');
INSERT INTO stu_map_bordertypes (id, faction_id, color, description) VALUES (10, 5, \'#FE9A2E\', \'Ferengi Allianz (Kerngebiet)\');
INSERT INTO stu_map_bordertypes (id, faction_id, color, description) VALUES (11, 6, \'#FFD39B\', \'Großmächte (Kerngebiet)\');
INSERT INTO stu_map_bordertypes (id, faction_id, color, description) VALUES (12, 6, \'#EEC591\', \'Großmächte (Randgebiet)\');
        ');
    }
}
