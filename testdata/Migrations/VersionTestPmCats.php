<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestPmCats extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_pm_cats.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (46, 10, \'Schiffe\', 2, 2, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (47, 10, \'Handel\', 4, 4, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (58, 1, \'Schiffe\', 2, 2, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (74, 11, \'Schiffe\', 2, 2, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (75, 11, \'Handel\', 4, 4, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (81, 12, \'Schiffe\', 2, 2, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (82, 12, \'Handel\', 4, 4, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (83, 10, \'Persönlich\', 2, 1, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (84, 10, \'Kolonien\', 3, 3, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (90, 12, \'Schiffe\', 2, 3, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4778, 101, \'Persönlich\', 1, 1, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4779, 101, \'Schiffe\', 2, 2, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4780, 101, \'Kolonien\', 3, 3, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4781, 101, \'Handel\', 4, 4, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4782, 101, \'Systemmeldungen\', 5, 5, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4783, 101, \'Postausgang\', 6, 6, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4784, 101, \'Stationen\', 7, 7, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3707, 13, \'127\', 13, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3753, 13, \'145\', 18, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4324, 11, \'GL - Kommunikation\', 9, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3736, 10, \'Erledigt\', 10, 0, 1716476607);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4348, 11, \'CVB - Kommunikation\', 11, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3696, 13, \'10\', 8, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (86, 11, \'Persönlich\', 2, 1, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (89, 12, \'Posteingang\', 4, 1, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (85, 10, \'Postausgang\', 2, 6, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (327, 15, \'Handel\', 4, 4, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (328, 15, \'Persönlich\', 2, 1, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (329, 15, \'Kolonien\', 3, 3, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (331, 15, \'Schiffe\', 2, 2, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4785, 102, \'Persönlich\', 1, 1, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4786, 102, \'Schiffe\', 2, 2, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4787, 102, \'Kolonien\', 3, 3, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4788, 102, \'Handel\', 4, 4, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4789, 102, \'Systemmeldungen\', 5, 5, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4790, 102, \'Postausgang\', 6, 6, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4791, 102, \'Stationen\', 7, 7, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (57, 1, \'Persönlich\', 2, 1, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (59, 1, \'Kolonien\', 3, 3, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (60, 1, \'Handel\', 4, 4, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (87, 11, \'Kolonien\', 4, 3, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4760, 10, \'Senatorin Neireh (111)\', 15, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4441, 13, \'202\', 25, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (921, 11, \'ROM - Taev\', 1, 0, 1677737778);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (952, 11, \'ROM - MisterX\', 1, 0, 1677737790);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (2870, 19, \'Postausgang\', 6, 6, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (2869, 19, \'Systemmeldungen\', 5, 5, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1346, 11, \'IKA & CO\', 1, 0, 1680439713);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1080, 10, \'Systemmeldungen\', 5, 5, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (2868, 19, \'Handel\', 4, 4, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (2867, 19, \'Kolonien\', 3, 3, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (2866, 19, \'Stationen\', 7, 7, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (2865, 19, \'Schiffe\', 2, 2, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (2864, 19, \'Persönlich\', 1, 1, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1084, 15, \'Systemmeldungen\', 5, 5, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (88, 11, \'Postausgang\', 4, 6, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (91, 12, \'Postausgang\', 2, 6, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (330, 15, \'Postausgang\', 2, 6, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (61, 1, \'Postausgang\', 2, 6, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4042, 13, \'Kampfhandlungen\', 1, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1003, 2, \'Systemmeldungen\', 5, 5, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1005, 13, \'Systemmeldungen\', 5, 5, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1009, 3, \'Systemmeldungen\', 5, 5, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1010, 1, \'Systemmeldungen\', 5, 5, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (326, 14, \'Schiffe\', 0, 2, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (323, 14, \'Persönlich\', 1, 1, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1161, 14, \'Stationen\', 2, 7, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (325, 14, \'Postausgang\', 3, 6, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (324, 14, \'Kolonien\', 4, 3, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (322, 14, \'Handel\', 5, 4, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1031, 12, \'Systemmeldungen\', 5, 5, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1047, 11, \'Systemmeldungen\', 5, 5, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1053, 14, \'Systemmeldungen\', 6, 5, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4761, 10, \'Takio Industries [ISA] (135)\', 16, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1154, 1, \'Stationen\', 6, 7, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1155, 2, \'Stationen\', 6, 7, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1156, 3, \'Stationen\', 6, 7, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1157, 10, \'Stationen\', 6, 7, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1158, 11, \'Stationen\', 6, 7, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1159, 12, \'Stationen\', 6, 7, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1162, 15, \'Stationen\', 6, 7, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1555, 13, \'Kampfhandlungen\', 1, 0, 1688469297);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (318, 13, \'Persönlich\', 2, 1, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (319, 13, \'Kolonien\', 3, 3, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (317, 13, \'Handel\', 4, 4, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1160, 13, \'Stationen\', 6, 7, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (320, 13, \'Postausgang\', 7, 6, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4320, 10, \'GL - Gespräche\', 11, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3704, 10, \'Projekt Ladrillero - fertig\', 7, 0, 1716476614);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4762, 10, \'{TB} Wasudharr-Republik (314)\', 17, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4749, 13, \'120\', 12, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3737, 13, \'135\', 14, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (321, 13, \'Schiffe\', 0, 2, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3688, 13, \'143\', 17, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4321, 10, \'DRK - Gespräche\', 12, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3705, 10, \'NPC - Gespräche\', 8, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4339, 11, \'LAT - Kommunikation\', 10, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4107, 17, \'Postausgang\', 6, 6, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4106, 17, \'Systemmeldungen\', 5, 5, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4105, 17, \'Handel\', 4, 4, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4104, 17, \'Kolonien\', 3, 3, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4103, 17, \'Stationen\', 7, 7, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4102, 17, \'Schiffe\', 2, 2, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4101, 17, \'Persönlich\', 1, 1, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4322, 11, \'Ablage - Alt\', 7, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3706, 10, \'Projekt Ladrillero - unvollständig\', 9, 0, 1716476619);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (2247, 11, \'RC\', 1, 0, 1675696413);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (2250, 11, \'Plot-IU\', 1, 0, 1670598961);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4251, 13, \'105\', 10, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3752, 13, \'138\', 15, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3672, 13, \'142\', 16, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4384, 13, \'169\', 21, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4401, 13, \'178\', 22, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (2501, 11, \'GP\', 1, 0, 1677737726);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (1410, 11, \'cyan\', 1, 0, 1677737771);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (2248, 11, \'Plot-Tribble\', 1, 0, 1680439707);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (2251, 11, \'Plot-Konstrukt\', 1, 0, 1680439711);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4262, 13, \'102\', 9, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4323, 11, \'ORG - Kommunikation\', 8, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4493, 13, \'149\', 19, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3992, 13, \'159\', 20, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4494, 13, \'183\', 23, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3768, 13, \'215\', 26, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (3867, 13, \'216\', 27, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4250, 13, \'224\', 29, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4758, 10, \'[T³] - Suraza Triade (119)\', 13, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4777, 11, \'NPC - Föd\', 14, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4470, 11, \'BAM - Kommunikation\', 12, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4497, 11, \'ENA - Kommunikation\', 13, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4665, 13, \'111\', 11, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4759, 10, \'Autarch Wulfgar [ISA] (183)\', 14, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4610, 13, \'194\', 24, 0, NULL);
INSERT INTO stu_pm_cats (id, user_id, description, sort, special, deleted) VALUES (4541, 13, \'222\', 28, 0, NULL);
        ');
    }
}
