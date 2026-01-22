<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestAllianceJobPermissions extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds founder permission for test president job.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_alliance_job_permission (job_id, permission) VALUES (1, 1);');
    }
}
