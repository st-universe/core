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
                VALUES (2, 6, 1, \'Crew\', 102, 3),
                       (3, 6, 1, \'Crew\', 102, 2),
                       (4, 6, 1, \'Crew\', 102, 3),
                       (5, 6, 2, \'Crew\', 102, 3),
                       (6, 6, 1, \'Crew\', 102, 12),
                       (7, 6, 1, \'Crew\', 102, 3),
                       (8, 6, 1, \'Crew\', 102, 2),
                       (9, 6, 2, \'Crew\', 102, 3),
                       (10, 6, 1, \'Crew\', 102, 2),
                       (11, 6, 1, \'Crew\', 102, 3),
                       (12, 6, 2, \'Crew\', 102, 1);
        ');
    }
}
