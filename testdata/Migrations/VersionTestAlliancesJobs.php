<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestAlliancesJobs extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_alliances_jobs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_alliances_jobs (id, alliance_id, user_id, type) VALUES (2, 2, 101, 1);
        ');
    }
}
