<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestResearched extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_researched.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_researched (id, research_id, user_id, aktiv, finished)
                VALUES (37842, 1001, 101, 0, 1731253491),
                       (37843, 1001, 102, 0, 1731253673);
        ');
    }
}
