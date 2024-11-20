<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestModulesSpecials extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_modules_specials.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (1, 11807, 2);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (2, 110, 42);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (3, 19101, 1);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (10, 19403, 4);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (11, 19805, 5);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (12, 19906, 6);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (14, 20017, 7);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (20, 21018, 8);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (26, 22009, 9);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (27, 22010, 10);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (43, 22170, 11);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (45, 22172, 12);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (44, 22171, 13);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (47, 22173, 14);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (48, 22174, 14);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (49, 22175, 15);
INSERT INTO stu_modules_specials (id, module_id, special_id) VALUES (50, 22176, 15);
        ');
    }
}
