<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestMap extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_map.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15247, 252, NULL, 134, 252, NULL, 1060);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14401, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14407, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14406, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14405, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14404, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14403, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14402, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14521, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14522, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14523, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14524, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14525, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14526, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14527, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14641, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14642, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14643, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14647, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14646, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14645, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14644, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14761, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14762, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14763, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14764, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14765, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14766, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14767, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14881, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14882, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14883, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14887, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14886, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14885, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (14884, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15001, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15121, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15122, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15002, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15003, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15123, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15124, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15125, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15126, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15127, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15007, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15006, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15005, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15004, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15241, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15246, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15245, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15244, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15243, NULL, NULL, 134, 252, NULL, NULL);
INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id) VALUES (15242, NULL, NULL, 134, 252, NULL, NULL);
        ');
    }
}
