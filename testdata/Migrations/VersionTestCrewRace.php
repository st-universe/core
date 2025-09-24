<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestCrewRace extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_crew_race.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define)
                VALUES (1, 1, \'Mensch\', 40, 55, \'HUMAN\'),
                       (2, 1, \'Vulkanier\', 30, 50, \'VULCAN\'),
                       (3, 1, \'Andorianer\', 25, 70, \'ANDORIAN\'),
                       (6, 2, \'Romulaner\', 25, 60, \'ROMULAN\'),
                       (8, 4, \'Cardassianer\', 25, 66, \'CARDASSIAN\'),
                       (9, 5, \'Ferengi\', 25, 100, \'FERENGI\'),
                       (10, 6, \'Pakled\', 25, 100, \'PAKLED\'),
                       (11, 1, \'Trill\', 20, 30, \'TRILL\'),
                       (12, 1, \'Bolianer\', 15, 60, \'BOLIAN\'),
                       (7, 3, \'Klingone\', 80, 80, \'KLINGON\'),
                       (13, 3, \'Gorn\', 20, 100, \'GORN\'),
                       (14, 8, \'Borg\', 100, 100, \'BORG\'),
                       (15, 7, \'Kazon\', 100, 100, \'KAZON\');
                       (16, 10, \'Hirogen\', 50, 100, \'HIROGEN\');
        ');
    }
}
