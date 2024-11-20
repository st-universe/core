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
        $this->addSql('INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (13, \'Phaser\', 10, 14, 0, 1, 1);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (14, \'Disruptor\', 15, 10, 0, 2, 3);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (15, \'Romulanischer Disruptor\', 14, 11, 0, 1, 10721);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (16, \'Romulanischer Disruptor\', 13, 12, 0, 1, 10722);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (17, \'Romulanischer Disruptor\', 12, 13, 0, 1, 10723);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (18, \'Romulanischer Disruptor\', 11, 14, 0, 1, 10724);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (19, \'Romulanischer Disruptor\', 10, 15, 0, 1, 10725);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (20, \'Romulanischer Disruptor\', 9, 16, 0, 1, 10726);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (21, \'Romulanischer Puls-Disruptor\', 14, 11, 0, 1, 11721);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (22, \'Romulanischer Puls-Disruptor\', 13, 12, 0, 1, 11722);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (23, \'Romulanischer Puls-Disruptor\', 12, 13, 0, 1, 11723);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (24, \'Romulanischer Puls-Disruptor\', 11, 14, 0, 1, 11724);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (25, \'Romulanischer Puls-Disruptor\', 10, 15, 0, 1, 11725);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (26, \'Romulanischer Puls-Disruptor\', 9, 16, 0, 1, 11726);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (27, \'Klingonischer Disruptor\', 14, 11, 0, 1, 10731);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (28, \'Klingonischer Disruptor\', 13, 12, 0, 1, 10732);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (29, \'Klingonischer Disruptor\', 12, 13, 0, 1, 10733);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (30, \'Klingonischer Disruptor\', 11, 14, 0, 1, 10734);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (31, \'Klingonischer Disruptor\', 10, 15, 0, 1, 10735);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (32, \'Klingonischer Disruptor\', 9, 16, 0, 1, 10736);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (33, \'Klingonischer Puls-Disruptor\', 14, 11, 0, 1, 11731);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (34, \'Klingonischer Puls-Disruptor\', 13, 12, 0, 1, 11732);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (35, \'Klingonischer Puls-Disruptor\', 12, 13, 0, 1, 11733);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (36, \'Klingonischer Puls-Disruptor\', 11, 14, 0, 1, 11734);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (37, \'Klingonischer Puls-Disruptor\', 10, 15, 0, 1, 11735);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (38, \'Klingonischer Puls-Disruptor\', 9, 16, 0, 1, 11736);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (39, \'Cardassianischer Spiralwellendisruptor\', 14, 11, 0, 1, 10741);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (40, \'Cardassianischer Spiralwellendisruptor\', 13, 12, 0, 1, 10742);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (41, \'Cardassianischer Spiralwellendisruptor\', 12, 13, 0, 1, 10743);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (42, \'Cardassianischer Spiralwellendisruptor\', 11, 14, 0, 1, 10744);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (43, \'Cardassianischer Spiralwellendisruptor\', 10, 15, 0, 1, 10745);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (44, \'Cardassianischer Spiralwellendisruptor\', 9, 16, 0, 1, 10746);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (45, \'Cardassianischer Puls-Disruptor\', 14, 11, 0, 1, 11741);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (46, \'Cardassianischer Puls-Disruptor\', 13, 12, 0, 1, 11742);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (47, \'Cardassianischer Puls-Disruptor\', 12, 13, 0, 1, 11743);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (48, \'Cardassianischer Puls-Disruptor\', 11, 14, 0, 1, 11744);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (49, \'Cardassianischer Puls-Disruptor\', 10, 15, 0, 1, 11745);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (50, \'Cardassianischer Puls-Disruptor\', 9, 16, 0, 1, 11746);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (51, \'Ferengi Phaser\', 14, 11, 0, 1, 10751);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (52, \'Ferengi Phaser\', 13, 12, 0, 1, 10752);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (53, \'Ferengi Phaser\', 12, 13, 0, 1, 10753);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (54, \'Ferengi Phaser\', 11, 14, 0, 1, 10754);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (55, \'Ferengi Phaser\', 10, 15, 0, 1, 10755);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (56, \'Ferengi Phaser\', 9, 16, 0, 1, 10756);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (57, \'Ferengi Puls-Phaser\', 14, 11, 0, 1, 11751);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (58, \'Ferengi Puls-Phaser\', 13, 12, 0, 1, 11752);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (59, \'Ferengi Puls-Phaser\', 12, 13, 0, 1, 11753);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (60, \'Ferengi Puls-Phaser\', 11, 14, 0, 1, 11754);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (61, \'Ferengi Puls-Phaser\', 10, 15, 0, 1, 11755);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (62, \'Ferengi Puls-Phaser\', 9, 16, 0, 1, 11756);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (1, \'Föderations Phaser\', 14, 11, 0, 1, 10701);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (2, \'Föderations Phaser\', 13, 12, 0, 1, 10702);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (3, \'Föderations Phaser\', 12, 13, 0, 1, 10703);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (4, \'Föderations Phaser\', 11, 14, 0, 1, 10704);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (5, \'Föderations Phaser\', 10, 15, 0, 1, 10705);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (6, \'Föderations Phaser\', 9, 16, 0, 1, 10706);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (7, \'Föderations Puls-Phaser\', 14, 11, 0, 1, 11701);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (8, \'Föderations Puls-Phaser\', 13, 12, 0, 1, 11702);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (9, \'Föderations Puls-Phaser\', 12, 13, 0, 1, 11703);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (10, \'Föderations Puls-Phaser\', 11, 14, 0, 1, 11704);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (11, \'Föderations Puls-Phaser\', 10, 15, 0, 1, 11705);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (12, \'Föderations Puls-Phaser\', 9, 16, 0, 1, 11706);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (63, \'Kazon Phaser\', 14, 11, 0, 1, 10771);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (64, \'Kazon Phaser\', 13, 12, 0, 1, 10772);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (65, \'Kazon Phaser\', 12, 13, 0, 1, 10773);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (66, \'Kazon Phaser\', 11, 14, 0, 1, 10774);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (67, \'Kazon Phaser\', 10, 15, 0, 1, 10775);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (68, \'Kazon Phaser\', 9, 16, 0, 1, 10776);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (69, \'Kazon Puls-Phaser\', 14, 11, 0, 1, 11771);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (70, \'Kazon Puls-Phaser\', 13, 12, 0, 1, 11772);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (71, \'Kazon Puls-Phaser\', 12, 13, 0, 1, 11773);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (72, \'Kazon Puls-Phaser\', 11, 14, 0, 1, 11774);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (73, \'Kazon Puls-Phaser\', 10, 15, 0, 1, 11775);
INSERT INTO stu_weapons (id, name, variance, critical_chance, type, firing_mode, module_id) VALUES (74, \'Kazon Puls-Phaser\', 9, 16, 0, 1, 11776);
        ');
    }
}
