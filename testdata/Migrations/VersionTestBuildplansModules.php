<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestBuildplansModules extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_buildplans_modules.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_buildplans_modules (id, buildplan_id, module_type, module_id, module_special, module_count) VALUES (25356, 2324, 1, 10102, NULL, 1),
                       (25357, 2324, 2, 10202, NULL, 1),
                       (25358, 2324, 3, 10302, NULL, 1),
                       (25359, 2324, 4, 10403, NULL, 1),
                       (25360, 2324, 5, 10502, NULL, 1),
                       (25361, 2324, 6, 11902, NULL, 1),
                       (25362, 2324, 7, 10702, NULL, 1),
                       (25363, 2324, 10, 10602, NULL, 1),
                       (25364, 2324, 11, 10912, NULL, 1),
                       (21178, 2075, 10, 10603, NULL, 1),
                       (17310, 2075, 6, 11902, NULL, 1),
                       (17305, 2075, 1, 10102, NULL, 1),
                       (17306, 2075, 2, 10202, NULL, 1),
                       (17307, 2075, 3, 10304, NULL, 1),
                       (17308, 2075, 4, 10402, NULL, 1),
                       (17309, 2075, 5, 10504, NULL, 1),
                       (17311, 2075, 7, 10702, NULL, 1),
                       (21040, 2075, 11, 10903, NULL, 1),
                       (22222, 689,1,11115,NULL,8);
                       (22223, 689,2,10225,NULL,10);
                       (22224, 689,3,10304,NULL,3);
                       (22225, 689,5,10504,NULL,4);
                       (22226, 689,6,11904,NULL,3);
                       (22227, 689,8,10835,NULL,3);
                       (22228, 689,10,10604,NULL,3);
                       (22229, 689,7,10735,NULL,3);
        ');
    }
}
