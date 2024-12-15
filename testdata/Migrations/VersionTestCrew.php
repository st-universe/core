<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestCrew extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_crew.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_crew (id, type, gender, name, user_id, race_id)
                VALUES (2, 6, 1, \'Crew\', 101, 3),
                       (3, 6, 1, \'Crew\', 101, 2),
                       (4, 6, 1, \'Crew\', 101, 3),
                       (5, 6, 2, \'Crew\', 101, 3),
                       (6, 6, 1, \'Crew\', 101, 12),
                       (7, 6, 1, \'Crew\', 101, 3),
                       (8, 6, 1, \'Crew\', 101, 2),
                       (9, 6, 2, \'Crew\', 101, 3),
                       (10, 6, 1, \'Crew\', 101, 2),
                       (11, 6, 1, \'Crew\', 101, 3),
                       (12, 6, 2, \'Crew\', 101, 1),
                       (13, 6, 2, \'Crew\', 101, 1);
        ');
    }
}
