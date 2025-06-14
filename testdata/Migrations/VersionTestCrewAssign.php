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
        $this->addSql('INSERT INTO stu_crew_assign (spacecraft_id, crew_id, slot, user_id, repair_task_id, colony_id, tradepost_id)
                VALUES (42, 2, NULL, 101, NULL, NULL, NULL),
                       (42, 3, NULL, 101, NULL, NULL, NULL),
                       (42, 4, NULL, 101, NULL, NULL, NULL),
                       (42, 5, NULL, 101, NULL, NULL, NULL),
                       (42, 6, NULL, 101, NULL, NULL, NULL),
                       (42, 7, NULL, 101, NULL, NULL, NULL),
                       (42, 8, NULL, 101, NULL, NULL, NULL),
                       (42, 9, NULL, 101, NULL, NULL, NULL),
                       (42, 10, NULL, 101, NULL, NULL, NULL),
                       (42, 11, NULL, 101, NULL, NULL, NULL),
                       (42, 12, NULL, 101, NULL, NULL, NULL),
                       (43, 13, NULL, 101, NULL, NULL, NULL);
        ');
    }
}
