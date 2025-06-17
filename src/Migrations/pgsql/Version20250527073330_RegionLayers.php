<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250527073330_RegionLayers extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add layers column to stu_map_regions table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_map_regions ADD layers VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_map_regions DROP layers');
    }
}
