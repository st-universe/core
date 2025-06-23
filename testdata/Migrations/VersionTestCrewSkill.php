<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestCrewSkill extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_crew_skill.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_crew_skill (crew_id,"position",expertise)
            VALUES (42,1,89),
                    (42,7,178);
        ');
    }
}
