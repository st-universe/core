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
        $this->addSql('INSERT INTO stu_modules_specials (id, module_id, special_id)
                VALUES (1, 11807, 2),
                       (2, 110, 42),
                       (3, 19101, 1),
                       (10, 19403, 4),
                       (11, 19805, 5),
                       (12, 19906, 6),
                       (14, 20017, 7),
                       (20, 21018, 8),
                       (26, 22009, 9),
                       (27, 22010, 10),
                       (43, 22170, 11),
                       (45, 22172, 12),
                       (44, 22171, 13),
                       (47, 22173, 14),
                       (48, 22174, 14),
                       (49, 22175, 15),
                       (50, 22176, 15);
        ');
    }
}
