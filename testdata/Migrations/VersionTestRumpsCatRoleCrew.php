<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestRumpsCatRoleCrew extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_rumps_cat_role_crew.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew)
                VALUES (11, 3, 3, 1, 1, 2, 0, 1, 1),
                       (12, 3, 4, 1, 1, 1, 2, 1, 1),
                       (1, 1, 1, 0, 0, 0, 0, 1, 1),
                       (2, 1, 2, 0, 0, 1, 0, 0, 1),
                       (3, 1, 3, 0, 0, 1, 0, 0, 1),
                       (5, 1, 5, 0, 0, 0, 0, 0, 0),
                       (4, 1, 4, 0, 0, 0, 1, 0, 1),
                       (10, 3, 2, 1, 1, 1, 0, 1, 2),
                       (9, 3, 1, 1, 1, 1, 0, 2, 1),
                       (25, 2, 6, 1, 0, 1, 0, 1, 1),
                       (6, 2, 1, 1, 0, 1, 0, 1, 1),
                       (8, 2, 3, 1, 0, 1, 0, 1, 1),
                       (7, 2, 2, 1, 0, 1, 0, 1, 1),
                       (21, 6, 7, 1, 0, 0, 0, 1, 1),
                       (22, 6, 8, 0, 0, 0, 1, 1, 0),
                       (28, 8, 3, 1, 1, 1, 1, 1, 1),
                       (19, 8, 1, 1, 4, 4, 4, 4, 2),
                       (26, 8, 2, 1, 0, 0, 1, 1, 1),
                       (29, 10, 9, 0, 0, 0, 0, 0, 0),
                       (35, 12, 11, 0, 1, 1, 0, 1, 0),
                       (30, 12, 12, 0, 1, 3, 0, 2, 0),
                       (34, 12, 13, 0, 1, 2, 1, 5, 0),
                       (33, 12, 14, 0, 1, 0, 2, 0, 0),
                       (32, 12, 15, 0, 2, 10, 3, 5, 0),
                       (31, 12, 16, 0, 5, 25, 5, 10, 0),
                       (37, 12, 17, 1, 1, 1, 1, 1, 1),
                       (16, 5, 1, 1, 3, 3, 2, 2, 2),
                       (24, 5, 4, 1, 3, 3, 2, 2, 2),
                       (17, 5, 2, 1, 3, 3, 2, 1, 3),
                       (18, 5, 3, 1, 3, 4, 1, 2, 2),
                       (14, 4, 2, 1, 2, 2, 1, 1, 1),
                       (13, 4, 1, 1, 2, 1, 1, 2, 1),
                       (15, 4, 3, 1, 2, 2, 0, 2, 1),
                       (23, 6, 5, 1, 0, 0, 0, 1, 1),
                       (20, 6, 6, 1, 0, 0, 0, 1, 1);
        ');
    }
}
