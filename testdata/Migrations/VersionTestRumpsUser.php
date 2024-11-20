<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestRumpsUser extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_rumps_user.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
        ');
    }
}
