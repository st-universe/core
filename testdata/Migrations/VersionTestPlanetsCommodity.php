<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestPlanetsCommodity extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_planets_commodity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (23, 411, 1508, 2);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (47, 201, 1508, 2);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (51, 301, 1508, 2);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (55, 203, 1508, 2);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (59, 303, 1508, 2);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (63, 205, 1508, 2);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (67, 305, 1508, 2);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (106, 219, 1512, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (107, 419, 1512, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (108, 211, 1512, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (109, 213, 1513, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (110, 413, 1513, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (111, 231, 1513, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (112, 211, 1514, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (113, 411, 1514, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (114, 213, 1514, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (115, 215, 1515, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (116, 415, 1515, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (117, 219, 1515, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (118, 231, 1516, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (119, 431, 1516, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (120, 215, 1516, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (74, 311, 1505, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (122, 219, 1505, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (124, 419, 1505, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (105, 417, 1519, 2);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (127, 417, 1521, 5);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (128, 217, 1521, 8);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (104, 217, 1519, 4);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (130, 417, 1511, 8);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (132, 221, 1520, 8);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (86, 215, 1505, 15);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (98, 231, 1505, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (99, 231, 1508, 4);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (97, 231, 1511, 21);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (102, 331, 1505, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (103, 331, 1508, 4);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (101, 331, 1511, 21);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (134, 331, 1513, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (135, 331, 1516, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (39, 431, 1508, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (38, 431, 1505, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (37, 431, 1511, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (75, 311, 1508, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (82, 313, 1505, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (83, 313, 1508, 4);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (69, 211, 1511, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (121, 219, 1508, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (85, 215, 1511, 9);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (125, 419, 1508, 2);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (29, 415, 1511, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (78, 213, 1505, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (79, 213, 1508, 4);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (77, 213, 1511, 18);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (73, 311, 1511, 18);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (81, 313, 1511, 18);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (136, 313, 1513, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (137, 313, 1514, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (26, 413, 1505, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (27, 413, 1508, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (25, 413, 1511, 9);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (70, 211, 1505, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (71, 211, 1508, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (21, 411, 1511, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (138, 311, 1512, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (139, 311, 1514, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (22, 411, 1505, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (144, 416, 1505, 9);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (54, 203, 1505, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (53, 203, 1511, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (58, 303, 1505, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (57, 303, 1511, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (10, 403, 1505, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (11, 403, 1508, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (9, 403, 1511, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (14, 404, 1505, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (15, 404, 1508, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (13, 404, 1511, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (46, 201, 1505, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (45, 201, 1511, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (50, 301, 1505, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (49, 301, 1511, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (2, 401, 1505, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (3, 401, 1508, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (1, 401, 1511, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (5, 402, 1511, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (7, 402, 1508, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (6, 402, 1505, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (62, 205, 1505, 18);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (61, 205, 1511, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (66, 305, 1505, 18);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (65, 305, 1511, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (18, 405, 1505, 9);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (19, 405, 1508, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (17, 405, 1511, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (142, 416, 1515, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (87, 215, 1508, 4);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (143, 416, 1511, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (94, 315, 1505, 18);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (95, 315, 1508, 4);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (93, 315, 1511, 15);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (140, 315, 1515, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (141, 315, 1516, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (30, 415, 1505, 9);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (31, 415, 1508, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (145, 416, 1508, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (126, 219, 1511, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (129, 217, 1511, 18);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (131, 221, 1505, 18);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (123, 419, 1511, 6);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (90, 216, 1505, 15);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (91, 216, 1508, 4);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (89, 216, 1511, 9);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (146, 216, 1516, 1);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (147, 216, 1515, 3);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (42, 421, 1505, 12);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (133, 421, 1520, 4);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (148, 317, 1521, 8);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (149, 317, 1519, 4);
INSERT INTO stu_planets_commodity (id, planet_classes_id, commodity_id, count) VALUES (150, 317, 1511, 18);
        ');
    }
}
