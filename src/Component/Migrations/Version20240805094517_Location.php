<?php

declare(strict_types=1);

namespace Stu\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240805094517_Location extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index on location entity location_coords_reverse_idx';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX location_coords_reverse_idx ON stu_location (layer_id, cy, cx)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX location_coords_reverse_idx');
    }
}
