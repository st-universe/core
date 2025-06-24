<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestColonyChangeable extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_colony_changeable.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_colony_changeable (colony_id, bev_work, bev_free, bev_max, eps, max_eps, max_storage, populationlimit, immigrationstate, shields, shield_frequency, torpedo_type)
                VALUES (76754, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76755, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76756, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76757, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76771, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76772, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76773, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76764, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76776, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76763, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76775, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76774, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76778, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76781, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76783, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76784, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76786, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76787, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76788, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76789, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76790, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76791, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76760, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76761, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76762, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76765, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76768, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76769, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76766, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (42, 0, 84, 84, 96, 96, 1000, 0, 1, 0, 0, NULL),
                       (76797, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76796, 20, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76780, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76767, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76782, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76792, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76752, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76770, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76759, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76793, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76779, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76795, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76753, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76785, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76794, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL),
                       (76758, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL);
        ');
    }
}
