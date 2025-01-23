<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestMapRegions extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_map_regions.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_map_regions (id, description, database_id) VALUES (134, \'Thalassanebel\', 6703434);
        ');
    }
}
