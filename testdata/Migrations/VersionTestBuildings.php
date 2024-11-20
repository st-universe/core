<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestBuildings extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_buildings.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_buildings (id, name, lager, eps_cost, eps, eps_proc, bev_pro, bev_use, integrity, research_id, view, buildtime, blimit, bclimit, is_activateable, bm_col) VALUES (82010100, \'Koloniezentrale\', 100, 120, 96, 16, 84, 0, 1500, 1001, true, 7200, 0, 1, true, 4);
        ');
    }
}
