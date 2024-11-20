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
        $this->addSql('INSERT INTO stu_rumps_categories (id, name, database_id, points)
                VALUES (1, \'Jäger/Runabout\', NULL, 3),
                       (2, \'Fregatte\', NULL, 6),
                       (3, \'Eskortschiff\', NULL, 12),
                       (4, \'Zerstörer\', NULL, 20),
                       (5, \'Kreuzer\', NULL, 30),
                       (6, \'Frachter\', NULL, 10),
                       (7, \'Trümmerfeld\', NULL, 0),
                       (8, \'Kriegsschiff\', NULL, 0),
                       (9, \'Rettungskapsel\', NULL, 0),
                       (10, \'Workbee\', NULL, 0),
                       (11, \'Konstruktion\', NULL, 0),
                       (12, \'Station\', NULL, 0),
                       (13, \'Energienetz\', NULL, 0);
        ');
    }
}
