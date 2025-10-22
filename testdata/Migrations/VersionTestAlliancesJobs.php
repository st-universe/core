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
        $this->addSql('INSERT INTO stu_alliances_jobs (id, alliance_id, title, sort, is_founder_permission, is_successor_permission, is_diplomatic_permission) 
            VALUES 
            (1, 2, "Präsident", 1, 1, 0, 0),
            (2, 2, "Vize-Präsident", 2, 0, 1, 0),
            (3, 2, "Außenminister", 3, 0, 0, 1);
        ');

        $this->addSql('INSERT INTO stu_alliance_member_job (id, user_id, job_id) VALUES (1, 101, 1);');
    }
}
