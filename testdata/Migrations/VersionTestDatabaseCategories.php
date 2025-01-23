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
        $this->addSql('INSERT INTO stu_database_categories (id, description, type, sort, points, prestige, award_id)
                VALUES (1, \'Schiffsrümpfe\', 1, 0, 1, 5, NULL),
                       (8, \'Forschung\', 8, 5, 1, 10, NULL),
                       (9, \'Stationsrümpfe\', 1, 1, 1, 5, NULL),
                       (2, \'RPG-Schiffe\', 2, 2, 2, 100, NULL),
                       (7, \'Sternensysteme\', 7, 3, 1, 10, 15),
                       (5, \'Planetentypen\', 7, 1, 1, 5, 20),
                       (6, \'Sternensystemtypen\', 7, 2, 2, 5, 21),
                       (4, \'Regionen\', 3, 4, 2, 5, 22),
                       (10, \'Planetentypenunused\', 0, 0, 0, 0, NULL),
                       (11, \'Sternensystemtypenunused\', 0, 0, 0, 0, NULL),
                       (12, \'Regionunused\', 0, 0, 0, 0, NULL),
                       (3, \'Handelsposten\', 3, 3, 1, 5, 23),
                       (13, \'sternensystemeunused\', 0, 0, 0, 0, NULL),
                       (14, \'unused\', 0, 0, 0, 0, NULL);
        ');
    }
}
