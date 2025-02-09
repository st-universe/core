<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250113065603_Crew_Assignments extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename crew assignment attribute.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_crew_assign RENAME COLUMN slot TO position');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_crew_assign RENAME COLUMN position TO slot');
    }
}
