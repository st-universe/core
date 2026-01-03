<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260103205722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_spacecraft RENAME COLUMN max_huelle TO max_hull');
        $this->addSql('ALTER TABLE stu_spacecraft RENAME COLUMN max_schilde TO max_shield');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_spacecraft RENAME COLUMN max_hull TO max_huelle');
        $this->addSql('ALTER TABLE stu_spacecraft RENAME COLUMN max_shield TO max_schilde');
    }
}
