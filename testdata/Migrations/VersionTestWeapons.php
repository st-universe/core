<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestWeapons extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_weapons.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id)
                VALUES (13, \'Phaser\', 10, 14, 0, 1, 1),
                       (14, \'Disruptor\', 15, 10, 0, 2, 3),
                       (15, \'Romulanischer Disruptor\', 14, 11, 0, 1, 10721),
                       (16, \'Romulanischer Disruptor\', 13, 12, 0, 1, 10722),
                       (17, \'Romulanischer Disruptor\', 12, 13, 0, 1, 10723),
                       (18, \'Romulanischer Disruptor\', 11, 14, 0, 1, 10724),
                       (19, \'Romulanischer Disruptor\', 10, 15, 0, 1, 10725),
                       (20, \'Romulanischer Disruptor\', 9, 16, 0, 1, 10726),
                       (21, \'Romulanischer Puls-Disruptor\', 14, 11, 0, 1, 11721),
                       (22, \'Romulanischer Puls-Disruptor\', 13, 12, 0, 1, 11722),
                       (23, \'Romulanischer Puls-Disruptor\', 12, 13, 0, 1, 11723),
                       (24, \'Romulanischer Puls-Disruptor\', 11, 14, 0, 1, 11724),
                       (25, \'Romulanischer Puls-Disruptor\', 10, 15, 0, 1, 11725),
                       (26, \'Romulanischer Puls-Disruptor\', 9, 16, 0, 1, 11726),
                       (27, \'Klingonischer Disruptor\', 14, 11, 0, 1, 10731),
                       (28, \'Klingonischer Disruptor\', 13, 12, 0, 1, 10732),
                       (29, \'Klingonischer Disruptor\', 12, 13, 0, 1, 10733),
                       (30, \'Klingonischer Disruptor\', 11, 14, 0, 1, 10734),
                       (31, \'Klingonischer Disruptor\', 10, 15, 0, 1, 10735),
                       (32, \'Klingonischer Disruptor\', 9, 16, 0, 1, 10736),
                       (33, \'Klingonischer Puls-Disruptor\', 14, 11, 0, 1, 11731),
                       (34, \'Klingonischer Puls-Disruptor\', 13, 12, 0, 1, 11732),
                       (35, \'Klingonischer Puls-Disruptor\', 12, 13, 0, 1, 11733),
                       (36, \'Klingonischer Puls-Disruptor\', 11, 14, 0, 1, 11734),
                       (37, \'Klingonischer Puls-Disruptor\', 10, 15, 0, 1, 11735),
                       (38, \'Klingonischer Puls-Disruptor\', 9, 16, 0, 1, 11736),
                       (39, \'Cardassianischer Spiralwellendisruptor\', 14, 11, 0, 1, 10741),
                       (40, \'Cardassianischer Spiralwellendisruptor\', 13, 12, 0, 1, 10742),
                       (41, \'Cardassianischer Spiralwellendisruptor\', 12, 13, 0, 1, 10743),
                       (42, \'Cardassianischer Spiralwellendisruptor\', 11, 14, 0, 1, 10744),
                       (43, \'Cardassianischer Spiralwellendisruptor\', 10, 15, 0, 1, 10745),
                       (44, \'Cardassianischer Spiralwellendisruptor\', 9, 16, 0, 1, 10746),
                       (45, \'Cardassianischer Puls-Disruptor\', 14, 11, 0, 1, 11741),
                       (46, \'Cardassianischer Puls-Disruptor\', 13, 12, 0, 1, 11742),
                       (47, \'Cardassianischer Puls-Disruptor\', 12, 13, 0, 1, 11743),
                       (48, \'Cardassianischer Puls-Disruptor\', 11, 14, 0, 1, 11744),
                       (49, \'Cardassianischer Puls-Disruptor\', 10, 15, 0, 1, 11745),
                       (50, \'Cardassianischer Puls-Disruptor\', 9, 16, 0, 1, 11746),
                       (51, \'Ferengi Phaser\', 14, 11, 0, 1, 10751),
                       (52, \'Ferengi Phaser\', 13, 12, 0, 1, 10752),
                       (53, \'Ferengi Phaser\', 12, 13, 0, 1, 10753),
                       (54, \'Ferengi Phaser\', 11, 14, 0, 1, 10754),
                       (55, \'Ferengi Phaser\', 10, 15, 0, 1, 10755),
                       (56, \'Ferengi Phaser\', 9, 16, 0, 1, 10756),
                       (57, \'Ferengi Puls-Phaser\', 14, 11, 0, 1, 11751),
                       (58, \'Ferengi Puls-Phaser\', 13, 12, 0, 1, 11752),
                       (59, \'Ferengi Puls-Phaser\', 12, 13, 0, 1, 11753),
                       (60, \'Ferengi Puls-Phaser\', 11, 14, 0, 1, 11754),
                       (61, \'Ferengi Puls-Phaser\', 10, 15, 0, 1, 11755),
                       (62, \'Ferengi Puls-Phaser\', 9, 16, 0, 1, 11756),
                       (1, \'Föderations Phaser\', 14, 11, 0, 1, 10701),
                       (2, \'Föderations Phaser\', 13, 12, 0, 1, 10702),
                       (3, \'Föderations Phaser\', 12, 13, 0, 1, 10703),
                       (4, \'Föderations Phaser\', 11, 14, 0, 1, 10704),
                       (5, \'Föderations Phaser\', 10, 15, 0, 1, 10705),
                       (6, \'Föderations Phaser\', 9, 16, 0, 1, 10706),
                       (7, \'Föderations Puls-Phaser\', 14, 11, 0, 1, 11701),
                       (8, \'Föderations Puls-Phaser\', 13, 12, 0, 1, 11702),
                       (9, \'Föderations Puls-Phaser\', 12, 13, 0, 1, 11703),
                       (10, \'Föderations Puls-Phaser\', 11, 14, 0, 1, 11704),
                       (11, \'Föderations Puls-Phaser\', 10, 15, 0, 1, 11705),
                       (12, \'Föderations Puls-Phaser\', 9, 16, 0, 1, 11706),
                       (63, \'Kazon Phaser\', 14, 11, 0, 1, 10771),
                       (64, \'Kazon Phaser\', 13, 12, 0, 1, 10772),
                       (65, \'Kazon Phaser\', 12, 13, 0, 1, 10773),
                       (66, \'Kazon Phaser\', 11, 14, 0, 1, 10774),
                       (67, \'Kazon Phaser\', 10, 15, 0, 1, 10775),
                       (68, \'Kazon Phaser\', 9, 16, 0, 1, 10776),
                       (69, \'Kazon Puls-Phaser\', 14, 11, 0, 1, 11771),
                       (70, \'Kazon Puls-Phaser\', 13, 12, 0, 1, 11772),
                       (71, \'Kazon Puls-Phaser\', 12, 13, 0, 1, 11773),
                       (72, \'Kazon Puls-Phaser\', 11, 14, 0, 1, 11774),
                       (73, \'Kazon Puls-Phaser\', 10, 15, 0, 1, 11775),
                       (74, \'Kazon Puls-Phaser\', 9, 16, 0, 1, 11776);
        ');
    }
}
