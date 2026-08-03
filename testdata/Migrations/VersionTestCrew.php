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
                VALUES (2, 7, 1, \'Crew\', 101, 3),
                       (3, 7, 1, \'Crew\', 101, 2),
                       (4, 7, 1, \'Crew\', 101, 3),
                       (5, 7, 2, \'Crew\', 101, 3),
                       (6, 7, 1, \'Crew\', 101, 12),
                       (7, 7, 1, \'Crew\', 101, 3),
                       (8, 7, 1, \'Crew\', 101, 2),
                       (9, 7, 2, \'Crew\', 101, 3),
                       (10, 7, 1, \'Crew\', 101, 2),
                       (11, 7, 1, \'Crew\', 101, 3),
                       (12, 7, 2, \'Crew\', 101, 1),
                       (13, 7, 2, \'Crew\', 101, 1),
                       (14, 7, 2, \'Crew\', 101, 1),
                       (15, 7, 2, \'Crew\', 101, 1),
                       (16, 7, 2, \'Crew\', 101, 1),
                       (17, 7, 2, \'Crew\', 101, 1),
                       (18, 7, 2, \'Crew\', 101, 1),
                       (19, 7, 2, \'Crew\', 101, 1),
                       (20, 7, 2, \'Crew\', 101, 1),
                       (21, 7, 2, \'Crew\', 101, 1),
                       (22, 7, 2, \'Crew\', 101, 1),
                       (23, 7, 2, \'Crew\', 101, 1),
                       (24, 7, 2, \'Crew\', 101, 1),
                       (25, 7, 2, \'Crew\', 101, 1),
                       (26, 7, 2, \'Crew\', 101, 1),
                       (27, 7, 2, \'Crew\', 101, 1),
                       (28, 7, 2, \'Crew\', 101, 1),
                       (29, 7, 2, \'Crew\', 101, 1),
                       (30, 7, 2, \'Crew\', 101, 1),
                       (31, 7, 2, \'Crew\', 101, 1),
                       (32, 7, 2, \'Crew\', 101, 1),
                       (33, 7, 2, \'Crew\', 101, 1),
                       (34, 7, 2, \'Crew\', 101, 1),
                       (35, 7, 2, \'Crew\', 101, 1),
                       (36, 7, 2, \'Crew\', 101, 1);
        ');
    }
}
