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
        $this->addSql('INSERT INTO stu_map (id, systems_id, bordertype_id, region_id, influence_area_id, admin_region_id, system_type_id)
                VALUES (15247, 252, NULL, 134, 252, NULL, 1060),
                       (14401, NULL, NULL, 134, 252, NULL, NULL),
                       (14407, NULL, NULL, 134, 252, NULL, NULL),
                       (14406, NULL, NULL, 134, 252, NULL, NULL),
                       (14405, NULL, NULL, 134, 252, NULL, NULL),
                       (14404, NULL, NULL, 134, 252, NULL, NULL),
                       (14403, NULL, NULL, 134, 252, NULL, NULL),
                       (14402, NULL, NULL, 134, 252, NULL, NULL),
                       (14521, NULL, NULL, 134, 252, NULL, NULL),
                       (14522, NULL, NULL, 134, 252, NULL, NULL),
                       (14523, NULL, NULL, 134, 252, NULL, NULL),
                       (14524, NULL, NULL, 134, 252, NULL, NULL),
                       (14525, NULL, NULL, 134, 252, NULL, NULL),
                       (14526, NULL, NULL, 134, 252, NULL, NULL),
                       (14527, NULL, NULL, 134, 252, NULL, NULL),
                       (14641, NULL, NULL, 134, 252, NULL, NULL),
                       (14642, NULL, NULL, 134, 252, NULL, NULL),
                       (14643, NULL, NULL, 134, 252, NULL, NULL),
                       (14647, NULL, NULL, 134, 252, NULL, NULL),
                       (14646, NULL, NULL, 134, 252, NULL, NULL),
                       (14645, NULL, NULL, 134, 252, NULL, NULL),
                       (14644, NULL, NULL, 134, 252, NULL, NULL),
                       (14761, NULL, NULL, 134, 252, NULL, NULL),
                       (14762, NULL, NULL, 134, 252, NULL, NULL),
                       (14763, NULL, NULL, 134, 252, NULL, NULL),
                       (14764, NULL, NULL, 134, 252, NULL, NULL),
                       (14765, NULL, NULL, 134, 252, NULL, NULL),
                       (14766, NULL, NULL, 134, 252, NULL, NULL),
                       (14767, NULL, NULL, 134, 252, NULL, NULL),
                       (14881, NULL, NULL, 134, 252, NULL, NULL),
                       (14882, NULL, NULL, 134, 252, NULL, NULL),
                       (14883, NULL, NULL, 134, 252, NULL, NULL),
                       (14887, NULL, NULL, 134, 252, NULL, NULL),
                       (14886, NULL, NULL, 134, 252, NULL, NULL),
                       (14885, NULL, NULL, 134, 252, NULL, NULL),
                       (14884, NULL, NULL, 134, 252, NULL, NULL),
                       (15001, NULL, NULL, 134, 252, NULL, NULL),
                       (15121, NULL, NULL, 134, 252, NULL, NULL),
                       (15122, NULL, NULL, 134, 252, NULL, NULL),
                       (15002, NULL, NULL, 134, 252, NULL, NULL),
                       (15003, NULL, NULL, 134, 252, NULL, NULL),
                       (15123, NULL, NULL, 134, 252, NULL, NULL),
                       (15124, NULL, NULL, 134, 252, NULL, NULL),
                       (15125, NULL, NULL, 134, 252, NULL, NULL),
                       (15126, NULL, NULL, 134, 252, NULL, NULL),
                       (15127, NULL, NULL, 134, 252, NULL, NULL),
                       (15007, NULL, NULL, 134, 252, NULL, NULL),
                       (15006, NULL, NULL, 134, 252, NULL, NULL),
                       (15005, NULL, NULL, 134, 252, NULL, NULL),
                       (15004, NULL, NULL, 134, 252, NULL, NULL),
                       (15241, NULL, NULL, 134, 252, NULL, NULL),
                       (15246, NULL, NULL, 134, 252, NULL, NULL),
                       (15245, NULL, NULL, 134, 252, NULL, NULL),
                       (15244, NULL, NULL, 134, 252, NULL, NULL),
                       (15243, NULL, NULL, 134, 252, NULL, NULL),
                       (15242, NULL, NULL, 134, 252, NULL, NULL);
        ');
    }
}
