<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestRumpsCategories extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_rumps_categories.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_rumps_categories (id, name, database_id, type)
                VALUES (1, \'Jäger/Runabout\', NULL, \'SHIP\'),
                       (2, \'Fregatte\', NULL, \'SHIP\'),
                       (3, \'Eskortschiff\', NULL, \'SHIP\'),
                       (4, \'Zerstörer\', NULL, \'SHIP\'),
                       (5, \'Kreuzer\', NULL, \'SHIP\'),
                       (6, \'Frachter\', NULL, \'SHIP\'),
                       (8, \'Kriegsschiff\', NULL, \'SHIP\'),
                       (9, \'Rettungskapsel\', NULL, \'SHIP\'),
                       (10, \'Workbee\', NULL, \'SHIP\'),
                       (11, \'Konstruktion\', NULL, \'STATION\'),
                       (12, \'Station\', NULL, \'STATION\'),
                       (13, \'Energienetz\', NULL, \'SHIP\');
        ');
    }
}
