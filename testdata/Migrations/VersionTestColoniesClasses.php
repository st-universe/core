<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestColoniesClasses extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds test colony classes.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_colonies_classes (id, name, database_id, colonizeable_fields, bev_growth_rate, special, allow_start, type, min_rot, max_rot)
            VALUES 
            (201, \'Klasse M\', 6703019, \'[101]\', 100, 0, true, 1, 90, 130),
            (203, \'Klasse L\', 6703021, \'[101]\', 100, 0, true, 1, 85, 115),
            (211, \'Klasse K\', 6703025, \'[601]\', 80, 0, false, 1, 95, 140),
            (215, \'Klasse P\', 6703029, \'[501]\', 65, 0, false, 1, 60, 85),
            (221, \'Klasse Q\', 6703037, \'[946]\', 40, 0, false, 1, 90, 110),
            (223, \'Klasse N\', 6703040, \'[]\', 30, 0, false, 1, 180, 230),
            (231, \'Klasse D\', 6703032, \'[715]\', 50, 0, false, 1, 15, 35),
            (401, \'Klasse M\', 6703002, \'[101]\', 100, 0, false, 2, 90, 130),
            (405, \'Klasse O\', 6703006, \'[101]\', 100, 0, false, 2, 96, 134),
            (413, \'Klasse H\', 6703008, \'[713]\', 70, 0, false, 2, 31, 60),
            (415, \'Klasse P\', 6703009, \'[501]\', 65, 0, false, 2, 60, 85),
            (431, \'Klasse D\', 6703011, \'[715]\', 50, 0, false, 2, 15, 35),
            (701, \'Dünnes Asteroidenfeld\', 67030053, \'[701080, 701220, 701270]\', 30, 0, false, 3, 2, 998),
            (702, \'Mittleres Asteroidenfeld\', 67030055, \'[702040, 702050, 702120, 702140, 702200]\', 30, 0, false, 3, 2, 1000),
            (703, \'Dichtes Asteroidenfeld\', 67030049, \'[703070, 703080, 703150, 703200, 703220, 703280]\', 30, 0, false, 3, 1, 999);'
        );
    }
}
