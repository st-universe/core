<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestTorpedoTypes extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_torpedo_types.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_torpedo_types (id, name, base_damage, critical_chance, hit_factor, hull_damage_factor, shield_damage_factor, variance, commodity_id, level, research_id, ecost, amount)
                VALUES (81, \'Micro-Photonentorpedo\', 12, 10, 100, 200, 100, 10, 81, 1, 500100, 6, 10),
                       (82, \'Leichter Photonentorpedo\', 24, 10, 100, 200, 100, 10, 82, 2, 500200, 7, 10),
                       (83, \'Photonentorpedo\', 48, 10, 100, 200, 100, 10, 83, 3, 500300, 8, 10),
                       (84, \'Quantentorpedo\', 90, 15, 100, 200, 100, 10, 84, 4, 501100, 9, 10),
                       (85, \'Schwerer Quantentorpedo\', 150, 20, 100, 200, 100, 15, 85, 5, 501200, 10, 10),
                       (86, \'Plasmatorpedo\', 80, 20, 100, 200, 100, 20, 86, 4, 502100, 9, 10),
                       (87, \'Schwerer Plasmatorpedo\', 150, 20, 100, 200, 100, 15, 87, 5, 502200, 10, 10);
        ');
    }
}
