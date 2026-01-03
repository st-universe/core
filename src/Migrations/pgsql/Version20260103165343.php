<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260103165343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_user_iptable RENAME COLUMN startdate TO start_date');
        $this->addSql('ALTER TABLE stu_user_iptable RENAME COLUMN enddate TO end_date');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_user_iptable RENAME COLUMN start_date TO startdate');
        $this->addSql('ALTER TABLE stu_user_iptable RENAME COLUMN end_date TO enddate');
    }
}
