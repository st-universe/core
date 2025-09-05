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
        $this->addSql('INSERT INTO stu_spacecraft (id, user_id, rump_id, plan_id, name, max_huelle, max_schilde, database_id, tractored_ship_id, holding_web_id, type, location_id)
            VALUES (42, 101, 6501, 2324, \'Aerie\', 819, 819, NULL, NULL, NULL, \'SHIP\', 15247),
                    (77, 101, 6501, 2324, \'Aerie Zwo\', 819, 819, NULL, NULL, NULL, \'SHIP\', 204359),
                    (78, 101, 6501, 2324, \'Aerie Three\', 819, 819, NULL, NULL, NULL, \'SHIP\', 204359),
                    (79, 10, 6501, 2324, \'Födi Aerie\', 819, 819, NULL, NULL, NULL, \'SHIP\', 204359),
                    (80, 10, 6501, 2324, \'Födi Aerie 2\', 819, 819, NULL, NULL, NULL, \'SHIP\', 15245),
                    (81, 101, 6501, 2324, \'Aerie mit Bussard\', 819, 819, NULL, NULL, NULL, \'SHIP\', 14887),
                    (43, 101, 10053, 689, \'Mighty AP\', 21000, 25000, NULL, NULL, NULL, \'STATION\', 15247),
                    (1021, 102, 6501, 2324, \'Aerie 102\', 819, 819, NULL, NULL, NULL, \'SHIP\', 204143),
                    (1022, 102, 10053, 689, \'AP 102\', 819, 819, NULL, NULL, NULL, \'STATION\', 204143),
                    (10203, 11, 6501, 2324, \'Aerie 102 at Mighty AP\', 819, 819, NULL, NULL, NULL, \'SHIP\', 15247),
                    (1023, 101, 3107, 1827, \'Netzschiff ohne Netz\', 819, 819, NULL, NULL, NULL, \'SHIP\', 204145),
                    (1024, 101, 3107, 1827, \'Netzschiff mit Netz in Bau\', 819, 819, NULL, NULL, NULL, \'SHIP\', 204146),
                    (1025, 101, 3107, 1827, \'Netzschiff am supporten\', 819, 819, NULL, NULL, NULL, \'SHIP\', 204146),
                    (1026, 101, 3107, 1827, \'Netzschiff mit fertigem Netz\', 819, 819, NULL, NULL, NULL, \'SHIP\', 204146),
                    (1031, 103, 6501, 2324, \'Aerie 103 Colonizer\', 819, 819, NULL, NULL, NULL, \'SHIP\', 204275),
                    (60001, 101, 9, 1840, \'Netz im Bau\', 819, 819, NULL, NULL, NULL, \'THOLIAN_WEB\', 204146),
                    (60002, 101, 9, 1840, \'Fertiges Netz\', 819, 819, NULL, NULL, NULL, \'THOLIAN_WEB\', 204147),
                    (100001, 101, 161, 667, \'Workbee\', 81, 81, NULL, NULL, NULL, \'SHIP\', 15247);
        ');
    }
}
