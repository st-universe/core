<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260903063034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename lastaction column to lastaction_timestamp in stu_user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_user RENAME COLUMN lastaction TO lastaction_timestamp');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_user RENAME COLUMN lastaction_timestamp TO lastaction');
    }
}
