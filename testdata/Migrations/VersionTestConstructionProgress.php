<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestConstructionProgress extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_progress_module.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_construction_progress (id, station_id,remaining_ticks)
                    VALUES (1, 43,0);
        ');
    }
}
