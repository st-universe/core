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
        $this->addSql('INSERT INTO stu_crew (id, rank, gender, name, user_id, race_id)
                VALUES (2, \'RECRUIT\', 1, \'Crew\', 101, 3),
                       (3, \'RECRUIT\', 1, \'Crew\', 101, 2),
                       (4, \'RECRUIT\', 1, \'Crew\', 101, 3),
                       (5, \'RECRUIT\', 2, \'Crew\', 101, 3),
                       (6, \'RECRUIT\', 1, \'Crew\', 101, 12),
                       (7, \'RECRUIT\', 1, \'Crew\', 101, 3),
                       (8, \'RECRUIT\', 1, \'Crew\', 101, 2),
                       (9, \'RECRUIT\', 2, \'Crew\', 101, 3),
                       (10, \'RECRUIT\', 1, \'Crew\', 101, 2),
                       (11, \'RECRUIT\', 1, \'Crew\', 101, 3),
                       (12, \'RECRUIT\', 2, \'Crew\', 101, 1),
                       (13, \'RECRUIT\', 2, \'Crew\', 101, 1),
                       (42, \'RECRUIT\', 2, \'Crew\', 101, 1);
        ');
    }
}
