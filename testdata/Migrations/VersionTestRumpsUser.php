<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestRumpsUser extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds user rumps.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_rumps_user (id, rump_id, user_id)
                VALUES (1, 6501, 101);
        ');
    }
}
