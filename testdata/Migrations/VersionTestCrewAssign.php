<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestCrewAssign extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_crew_assign.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_crew_assign (id, ship_id, crew_id, slot, user_id, repair_task_id, colony_id, tradepost_id)
                VALUES (2, 3, 2, NULL, 102, NULL, NULL, NULL),
                       (3, 3, 3, NULL, 102, NULL, NULL, NULL),
                       (4, 3, 4, NULL, 102, NULL, NULL, NULL),
                       (5, 3, 5, NULL, 102, NULL, NULL, NULL),
                       (6, 3, 6, NULL, 102, NULL, NULL, NULL),
                       (7, 3, 7, NULL, 102, NULL, NULL, NULL),
                       (8, 3, 8, NULL, 102, NULL, NULL, NULL),
                       (9, 3, 9, NULL, 102, NULL, NULL, NULL),
                       (10, 3, 10, NULL, 102, NULL, NULL, NULL),
                       (11, 3, 11, NULL, 102, NULL, NULL, NULL),
                       (12, 3, 12, NULL, 102, NULL, NULL, NULL);
        ');
    }
}
