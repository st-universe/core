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
        $this->addSql('INSERT INTO stu_database_types (id, description, macro) VALUES (1, \'Schiffsrümpfe\', \'shiprump\');
INSERT INTO stu_database_types (id, description, macro) VALUES (3, \'Point of Interest\', \'poiinfo\');
INSERT INTO stu_database_types (id, description, macro) VALUES (4, \'Sternensysteme\', \'starsystem\');
INSERT INTO stu_database_types (id, description, macro) VALUES (5, \'Sternensystemtypen\', \'starsystemtype\');
INSERT INTO stu_database_types (id, description, macro) VALUES (6, \'Planetentypen\', \'planettype\');
INSERT INTO stu_database_types (id, description, macro) VALUES (7, \'Karte\', \'regioninfo\');
INSERT INTO stu_database_types (id, description, macro) VALUES (8, \'Forschung\', \'research\');
        ');
    }
}
