<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241223144855_Increase_Range extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Field module_special of stu_buildplans_modules changed from smallint to integer';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_buildplans_modules ALTER module_special TYPE INT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_buildplans_modules ALTER module_special TYPE SMALLINT');
    }
}
