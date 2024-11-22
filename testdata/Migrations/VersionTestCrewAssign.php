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
                VALUES (2, 42, 2, NULL, 102, NULL, NULL, NULL),
                       (3, 42, 3, NULL, 102, NULL, NULL, NULL),
                       (4, 42, 4, NULL, 102, NULL, NULL, NULL),
                       (5, 42, 5, NULL, 102, NULL, NULL, NULL),
                       (6, 42, 6, NULL, 102, NULL, NULL, NULL),
                       (7, 42, 7, NULL, 102, NULL, NULL, NULL),
                       (8, 42, 8, NULL, 102, NULL, NULL, NULL),
                       (9, 42, 9, NULL, 102, NULL, NULL, NULL),
                       (10, 42, 10, NULL, 102, NULL, NULL, NULL),
                       (11, 42, 11, NULL, 102, NULL, NULL, NULL),
                       (12, 42, 12, NULL, 102, NULL, NULL, NULL);
        ');
    }
}
