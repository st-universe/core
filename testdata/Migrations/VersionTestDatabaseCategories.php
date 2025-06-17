<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestDatabaseCategories extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_database_categories.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_database_categories (id, description, type, sort, points, prestige)
                VALUES (1, \'Schiffsrümpfe\', 1, 0, 1, 5),
                       (8, \'Forschung\', 8, 5, 1, 10),
                       (9, \'Stationsrümpfe\', 1, 1, 1, 5),
                       (2, \'RPG-Schiffe\', 2, 2, 2, 100),
                       (7, \'Sternensysteme\', 7, 3, 1, 10),
                       (5, \'Planetentypen\', 7, 1, 1, 5),
                       (6, \'Sternensystemtypen\', 7, 2, 2, 5),
                       (4, \'Regionen\', 3, 4, 2, 5),
                       (10, \'Planetentypenunused\', 0, 0, 0, 0),
                       (11, \'Sternensystemtypenunused\', 0, 0, 0, 0),
                       (12, \'Regionunused\', 0, 0, 0, 0),
                       (3, \'Handelsposten\', 3, 3, 1, 5),
                       (13, \'sternensystemeunused\', 0, 0, 0, 0),
                       (14, \'unused\', 0, 0, 0, 0);
        ');
    }
}
