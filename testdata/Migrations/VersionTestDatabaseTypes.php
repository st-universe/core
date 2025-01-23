<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestDatabaseTypes extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_database_types.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_database_types (id, description, macro)
                VALUES (1, \'Schiffsrümpfe\', \'shiprump\'),
                       (3, \'Point of Interest\', \'poiinfo\'),
                       (4, \'Sternensysteme\', \'starsystem\'),
                       (5, \'Sternensystemtypen\', \'starsystemtype\'),
                       (6, \'Planetentypen\', \'planettype\'),
                       (7, \'Karte\', \'regioninfo\'),
                       (8, \'Forschung\', \'research\');
        ');
    }
}
