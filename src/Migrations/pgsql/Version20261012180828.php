<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012180828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_buildings ADD rotation BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE stu_colonies_fielddata ADD rotation INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_buildings DROP rotation');
        $this->addSql('ALTER TABLE stu_colonies_fielddata DROP rotation');
    }
}
