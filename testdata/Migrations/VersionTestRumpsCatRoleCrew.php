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
        $this->addSql('INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (11, 3, 3, 1, 2, 0, 1, 1, 20, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (12, 3, 4, 1, 1, 2, 1, 1, 19, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (1, 1, 1, 0, 0, 0, 1, 1, 3, 0);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (2, 1, 2, 0, 1, 0, 0, 1, 3, 0);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (3, 1, 3, 0, 1, 0, 0, 1, 3, 0);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (5, 1, 5, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (4, 1, 4, 0, 0, 1, 0, 1, 3, 0);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (10, 3, 2, 1, 1, 0, 1, 2, 20, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (9, 3, 1, 1, 1, 0, 2, 1, 20, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (25, 2, 6, 0, 1, 0, 1, 1, 10, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (6, 2, 1, 0, 1, 0, 1, 1, 10, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (8, 2, 3, 0, 1, 0, 1, 1, 10, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (7, 2, 2, 0, 1, 0, 1, 1, 10, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (21, 6, 7, 0, 0, 0, 1, 1, 14, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (22, 6, 8, 0, 0, 1, 1, 0, 0, 0);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (28, 8, 3, 1, 1, 1, 1, 1, 20, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (19, 8, 1, 4, 4, 4, 4, 2, 33, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (26, 8, 2, 0, 0, 1, 1, 1, -8, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (29, 10, 9, 0, 0, 0, 0, 0, 1, 0);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (35, 12, 11, 1, 1, 0, 1, 0, 2, 0);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (30, 12, 12, 1, 3, 0, 2, 0, 11, 0);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (34, 12, 13, 1, 2, 1, 5, 0, 1, 0);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (33, 12, 14, 1, 0, 2, 0, 0, 3, 0);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (32, 12, 15, 2, 10, 3, 5, 0, 5, 0);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (31, 12, 16, 5, 25, 5, 10, 0, 35, 0);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (37, 12, 17, 1, 1, 1, 1, 1, 1, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (16, 5, 1, 3, 3, 2, 2, 2, 33, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (24, 5, 4, 3, 3, 2, 2, 2, 33, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (17, 5, 2, 3, 3, 2, 1, 3, 33, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (18, 5, 3, 3, 4, 1, 2, 2, 33, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (14, 4, 2, 2, 2, 1, 1, 1, 27, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (13, 4, 1, 2, 1, 1, 2, 1, 27, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (15, 4, 3, 2, 2, 0, 2, 1, 27, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (23, 6, 5, 0, 0, 0, 1, 1, 14, 1);
INSERT INTO stu_rumps_cat_role_crew (id, rump_category_id, rump_role_id, job_1_crew, job_2_crew, job_3_crew, job_4_crew, job_5_crew, job_6_crew, job_7_crew) VALUES (20, 6, 6, 0, 0, 0, 1, 1, 14, 1);
        ');
    }
}
