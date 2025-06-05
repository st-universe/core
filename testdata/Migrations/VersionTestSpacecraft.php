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
        $this->addSql('INSERT INTO stu_spacecraft (id, user_id, rump_id, plan_id, name, huelle, max_huelle, schilde, max_schilde, database_id, disabled, state, tractored_ship_id, lss_mode, holding_web_id, type, location_id)
            VALUES (42, 101, 6501, 2324, \'Aerie\', 819, 819, 819, 819, NULL, 0, 0, NULL, 1, NULL, \'SHIP\', 15247),
                    (77, 101, 6501, 2324, \'Aerie Zwo\', 819, 819, 819, 819, NULL, 0, 0, NULL, 1, NULL, \'SHIP\', 204359),
                    (78, 101, 6501, 2324, \'Aerie Three\', 819, 819, 819, 819, NULL, 0, 0, NULL, 1, NULL, \'SHIP\', 204359),
                    (79, 10, 6501, 2324, \'Födi Aerie\', 819, 819, 819, 819, NULL, 0, 0, NULL, 1, NULL, \'SHIP\', 204359),
                    (43, 101, 10053, 689, \'Mighty AP\', 20000, 21000, 24000, 25000, NULL, 0, 0, NULL, 1, NULL, \'STATION\', 15247),
                    (1021, 102, 6501, 2324, \'Aerie 102\', 819, 819, 819, 819, NULL, 0, 0, NULL, 1, NULL, \'SHIP\', 204143),
                    (1022, 102, 10053, 689, \'AP 102\', 819, 819, 819, 819, NULL, 0, 0, NULL, 1, NULL, \'STATION\', 204143);
        ');
    }
}
