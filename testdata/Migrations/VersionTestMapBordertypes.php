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
        $this->addSql('INSERT INTO stu_map_bordertypes (id, faction_id, color, description)
                VALUES (1, 1, \'#000099\', \'Außengebiet (Föderation)\'),
                       (2, 1, \'#0000ff\', \'Kerngebiet (Föderation)\'),
                       (3, 2, \'#009900\', \'Romulanisches Imperium (Außengebiet)\'),
                       (4, 2, \'#00ff00\', \'Romulanisches Imperium (Kerngebiet)\'),
                       (5, 3, \'#990000\', \'Klingonisches Imperium (Außengebiet)\'),
                       (6, 3, \'#ff0000\', \'Klingonisches Imperium (Kerngebiet)\'),
                       (7, 4, \'#999900\', \'Cardassianische Union (Außengebiet)\'),
                       (8, 4, \'#ffff00\', \'Cardassianische Union (Kerngebiet)\'),
                       (9, 5, \'#DF7401\', \'Ferengi Allianz (Außengebiet)\'),
                       (10, 5, \'#FE9A2E\', \'Ferengi Allianz (Kerngebiet)\'),
                       (11, 6, \'#FFD39B\', \'Großmächte (Kerngebiet)\'),
                       (12, 6, \'#EEC591\', \'Großmächte (Randgebiet)\');
        ');
    }
}
