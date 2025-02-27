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
        $this->addSql('INSERT INTO stu_colonies_fielddata (id, colonies_id, field_id, type_id, buildings_id, terraforming_id, integrity, aktiv, activate_after_build, colony_sandbox_id)
                VALUES (2, 42, 0, 900, NULL, NULL, 0, 0, 1, NULL),
                       (3, 42, 1, 900, NULL, NULL, 0, 0, 1, NULL),
                       (4, 42, 2, 900, NULL, NULL, 0, 0, 1, NULL),
                       (5, 42, 3, 900, NULL, NULL, 0, 0, 1, NULL),
                       (6, 42, 4, 900, NULL, NULL, 0, 0, 1, NULL),
                       (7, 42, 5, 900, NULL, NULL, 0, 0, 1, NULL),
                       (8, 42, 6, 900, NULL, NULL, 0, 0, 1, NULL),
                       (9, 42, 7, 900, NULL, NULL, 0, 0, 1, NULL),
                       (10, 42, 8, 900, NULL, NULL, 0, 0, 1, NULL),
                       (11, 42, 9, 900, NULL, NULL, 0, 0, 1, NULL),
                       (12, 42, 10, 900, NULL, NULL, 0, 0, 1, NULL),
                       (13, 42, 11, 900, NULL, NULL, 0, 0, 1, NULL),
                       (14, 42, 12, 900, NULL, NULL, 0, 0, 1, NULL),
                       (15, 42, 13, 900, NULL, NULL, 0, 0, 1, NULL),
                       (16, 42, 14, 900, NULL, NULL, 0, 0, 1, NULL),
                       (17, 42, 15, 900, NULL, NULL, 0, 0, 1, NULL),
                       (18, 42, 16, 900, NULL, NULL, 0, 0, 1, NULL),
                       (19, 42, 17, 900, NULL, NULL, 0, 0, 1, NULL),
                       (20, 42, 18, 900, NULL, NULL, 0, 0, 1, NULL),
                       (21, 42, 19, 900, NULL, NULL, 0, 0, 1, NULL),
                       (22, 42, 20, 701, NULL, NULL, 0, 0, 1, NULL),
                       (23, 42, 21, 701, NULL, NULL, 0, 0, 1, NULL),
                       (24, 42, 22, 501, NULL, NULL, 0, 0, 1, NULL),
                       (25, 42, 23, 201, NULL, NULL, 0, 0, 1, NULL),
                       (27, 42, 25, 112, NULL, NULL, 0, 0, 1, NULL),
                       (28, 42, 26, 501, NULL, NULL, 0, 0, 1, NULL),
                       (29, 42, 27, 112, NULL, NULL, 0, 0, 1, NULL),
                       (30, 42, 28, 112, NULL, NULL, 0, 0, 1, NULL),
                       (32, 42, 30, 111, NULL, NULL, 0, 0, 1, NULL),
                       (33, 42, 31, 701, NULL, NULL, 0, 0, 1, NULL),
                       (34, 42, 32, 101, NULL, NULL, 0, 0, 1, NULL),
                       (35, 42, 33, 101, NULL, NULL, 0, 0, 1, NULL),
                       (36, 42, 34, 101, NULL, NULL, 0, 0, 1, NULL),
                       (37, 42, 35, 201, NULL, NULL, 0, 0, 1, NULL),
                       (38, 42, 36, 201, NULL, NULL, 0, 0, 1, NULL),
                       (39, 42, 37, 111, NULL, NULL, 0, 0, 1, NULL),
                       (40, 42, 38, 701, NULL, NULL, 0, 0, 1, NULL),
                       (41, 42, 39, 101, NULL, NULL, 0, 0, 1, NULL),
                       (42, 42, 40, 101, NULL, NULL, 0, 0, 1, NULL),
                       (43, 42, 41, 101, NULL, NULL, 0, 0, 1, NULL),
                       (44, 42, 42, 101, NULL, NULL, 0, 0, 1, NULL),
                       (45, 42, 43, 401, NULL, NULL, 0, 0, 1, NULL),
                       (46, 42, 44, 101, NULL, NULL, 0, 0, 1, NULL),
                       (47, 42, 45, 201, NULL, NULL, 0, 0, 1, NULL),
                       (48, 42, 46, 111, NULL, NULL, 0, 0, 1, NULL),
                       (49, 42, 47, 111, NULL, NULL, 0, 0, 1, NULL),
                       (50, 42, 48, 111, NULL, NULL, 0, 0, 1, NULL),
                       (51, 42, 49, 111, NULL, NULL, 0, 0, 1, NULL),
                       (52, 42, 50, 701, NULL, NULL, 0, 0, 1, NULL),
                       (53, 42, 51, 401, NULL, NULL, 0, 0, 1, NULL),
                       (54, 42, 52, 401, NULL, NULL, 0, 0, 1, NULL),
                       (55, 42, 53, 101, NULL, NULL, 0, 0, 1, NULL),
                       (56, 42, 54, 101, NULL, NULL, 0, 0, 1, NULL),
                       (57, 42, 55, 111, NULL, NULL, 0, 0, 1, NULL),
                       (58, 42, 56, 111, NULL, NULL, 0, 0, 1, NULL),
                       (59, 42, 57, 201, NULL, NULL, 0, 0, 1, NULL),
                       (60, 42, 58, 201, NULL, NULL, 0, 0, 1, NULL),
                       (61, 42, 59, 201, NULL, NULL, 0, 0, 1, NULL),
                       (62, 42, 60, 101, NULL, NULL, 0, 0, 1, NULL),
                       (63, 42, 61, 101, NULL, NULL, 0, 0, 1, NULL),
                       (64, 42, 62, 701, NULL, NULL, 0, 0, 1, NULL),
                       (65, 42, 63, 701, NULL, NULL, 0, 0, 1, NULL),
                       (66, 42, 64, 201, NULL, NULL, 0, 0, 1, NULL),
                       (67, 42, 65, 201, NULL, NULL, 0, 0, 1, NULL),
                       (68, 42, 66, 201, NULL, NULL, 0, 0, 1, NULL),
                       (69, 42, 67, 201, NULL, NULL, 0, 0, 1, NULL),
                       (70, 42, 68, 201, NULL, NULL, 0, 0, 1, NULL),
                       (71, 42, 69, 201, NULL, NULL, 0, 0, 1, NULL),
                       (72, 42, 70, 501, NULL, NULL, 0, 0, 1, NULL),
                       (73, 42, 71, 101, NULL, NULL, 0, 0, 1, NULL),
                       (74, 42, 72, 112, NULL, NULL, 0, 0, 1, NULL),
                       (75, 42, 73, 501, NULL, NULL, 0, 0, 1, NULL),
                       (76, 42, 74, 501, NULL, NULL, 0, 0, 1, NULL),
                       (77, 42, 75, 201, NULL, NULL, 0, 0, 1, NULL),
                       (78, 42, 76, 201, NULL, NULL, 0, 0, 1, NULL),
                       (79, 42, 77, 201, NULL, NULL, 0, 0, 1, NULL),
                       (80, 42, 78, 201, NULL, NULL, 0, 0, 1, NULL),
                       (81, 42, 79, 201, NULL, NULL, 0, 0, 1, NULL),
                       (82, 42, 80, 802, NULL, NULL, 0, 0, 1, NULL),
                       (83, 42, 81, 801, NULL, NULL, 0, 0, 1, NULL),
                       (84, 42, 82, 801, NULL, NULL, 0, 0, 1, NULL),
                       (85, 42, 83, 801, NULL, NULL, 0, 0, 1, NULL),
                       (86, 42, 84, 851, NULL, NULL, 0, 0, 1, NULL),
                       (87, 42, 85, 851, NULL, NULL, 0, 0, 1, NULL),
                       (88, 42, 86, 851, NULL, NULL, 0, 0, 1, NULL),
                       (89, 42, 87, 802, NULL, NULL, 0, 0, 1, NULL),
                       (90, 42, 88, 802, NULL, NULL, 0, 0, 1, NULL),
                       (91, 42, 89, 801, NULL, NULL, 0, 0, 1, NULL),
                       (92, 42, 90, 802, NULL, NULL, 0, 0, 1, NULL),
                       (93, 42, 91, 801, NULL, NULL, 0, 0, 1, NULL),
                       (94, 42, 92, 801, NULL, NULL, 0, 0, 1, NULL),
                       (95, 42, 93, 802, NULL, NULL, 0, 0, 1, NULL),
                       (96, 42, 94, 801, NULL, NULL, 0, 0, 1, NULL),
                       (97, 42, 95, 851, NULL, NULL, 0, 0, 1, NULL),
                       (98, 42, 96, 851, NULL, NULL, 0, 0, 1, NULL),
                       (99, 42, 97, 802, NULL, NULL, 0, 0, 1, NULL),
                       (100, 42, 98, 801, NULL, NULL, 0, 0, 1, NULL),
                       (101, 42, 99, 801, NULL, NULL, 0, 0, 1, NULL),
                       (31, 42, 29, 112, NULL, NULL, 0, 0, 1, NULL),
                       (26, 42, 24, 101, 82010100, NULL, 1500, 1, 1, NULL);
        ');
    }
}
