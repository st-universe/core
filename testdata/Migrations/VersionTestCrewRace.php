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
        $this->addSql('INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define) VALUES (1, 1, \'Mensch\', 40, 55, \'HUMAN\');
INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define) VALUES (2, 1, \'Vulkanier\', 30, 50, \'VULCAN\');
INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define) VALUES (3, 1, \'Andorianer\', 25, 70, \'ANDORIAN\');
INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define) VALUES (6, 2, \'Romulaner\', 25, 60, \'ROMULAN\');
INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define) VALUES (8, 4, \'Cardassianer\', 25, 66, \'CARDASSIAN\');
INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define) VALUES (9, 5, \'Ferengi\', 25, 100, \'FERENGI\');
INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define) VALUES (10, 6, \'Pakled\', 25, 100, \'PAKLED\');
INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define) VALUES (11, 1, \'Trill\', 20, 30, \'TRILL\');
INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define) VALUES (12, 1, \'Bolianer\', 15, 60, \'BOLIAN\');
INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define) VALUES (7, 3, \'Klingone\', 80, 80, \'KLINGON\');
INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define) VALUES (13, 3, \'Gorn\', 20, 100, \'GORN\');
INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define) VALUES (14, 8, \'Borg\', 100, 100, \'BORG\');
INSERT INTO stu_crew_race (id, faction_id, description, chance, maleratio, define) VALUES (15, 7, \'Kazon\', 100, 100, \'KAZON\');
        ');
    }
}
