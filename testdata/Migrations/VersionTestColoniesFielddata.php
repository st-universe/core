<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestColoniesFielddata extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_colonies_fielddata.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (2, 76777, 0, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (3, 76777, 1, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (4, 76777, 2, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (5, 76777, 3, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (6, 76777, 4, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (7, 76777, 5, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (8, 76777, 6, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (9, 76777, 7, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (10, 76777, 8, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (11, 76777, 9, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (12, 76777, 10, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (13, 76777, 11, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (14, 76777, 12, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (15, 76777, 13, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (16, 76777, 14, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (17, 76777, 15, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (18, 76777, 16, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (19, 76777, 17, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (20, 76777, 18, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (21, 76777, 19, 900, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (22, 76777, 20, 701, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (23, 76777, 21, 701, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (24, 76777, 22, 501, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (25, 76777, 23, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (27, 76777, 25, 112, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (28, 76777, 26, 501, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (29, 76777, 27, 112, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (30, 76777, 28, 112, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (32, 76777, 30, 111, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (33, 76777, 31, 701, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (34, 76777, 32, 101, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (35, 76777, 33, 101, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (36, 76777, 34, 101, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (37, 76777, 35, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (38, 76777, 36, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (39, 76777, 37, 111, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (40, 76777, 38, 701, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (41, 76777, 39, 101, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (42, 76777, 40, 101, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (43, 76777, 41, 101, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (44, 76777, 42, 101, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (45, 76777, 43, 401, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (46, 76777, 44, 101, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (47, 76777, 45, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (48, 76777, 46, 111, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (49, 76777, 47, 111, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (50, 76777, 48, 111, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (51, 76777, 49, 111, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (52, 76777, 50, 701, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (53, 76777, 51, 401, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (54, 76777, 52, 401, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (55, 76777, 53, 101, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (56, 76777, 54, 101, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (57, 76777, 55, 111, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (58, 76777, 56, 111, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (59, 76777, 57, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (60, 76777, 58, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (61, 76777, 59, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (62, 76777, 60, 101, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (63, 76777, 61, 101, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (64, 76777, 62, 701, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (65, 76777, 63, 701, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (66, 76777, 64, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (67, 76777, 65, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (68, 76777, 66, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (69, 76777, 67, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (70, 76777, 68, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (71, 76777, 69, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (72, 76777, 70, 501, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (73, 76777, 71, 101, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (74, 76777, 72, 112, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (75, 76777, 73, 501, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (76, 76777, 74, 501, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (77, 76777, 75, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (78, 76777, 76, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (79, 76777, 77, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (80, 76777, 78, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (81, 76777, 79, 201, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (82, 76777, 80, 802, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (83, 76777, 81, 801, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (84, 76777, 82, 801, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (85, 76777, 83, 801, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (86, 76777, 84, 851, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (87, 76777, 85, 851, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (88, 76777, 86, 851, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (89, 76777, 87, 802, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (90, 76777, 88, 802, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (91, 76777, 89, 801, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (92, 76777, 90, 802, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (93, 76777, 91, 801, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (94, 76777, 92, 801, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (95, 76777, 93, 802, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (96, 76777, 94, 801, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (97, 76777, 95, 851, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (98, 76777, 96, 851, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (99, 76777, 97, 802, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (100, 76777, 98, 801, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (101, 76777, 99, 801, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (31, 76777, 29, 112, NULL, NULL, 0, 0, true, NULL);
INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id) VALUES (26, 76777, 24, 101, 82010100, NULL, 1500, 1, true, NULL);
        ');
    }
}
