<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260803130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Moves maximum crew capacity to rump base values and removes the regular crew position.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_rump_base_values ADD max_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql(<<<'SQL'
                    UPDATE stu_rump_base_values rbv
                    SET max_crew = rbv.base_crew
                        + COALESCE(rc.job_1_crew, 0)
                        + COALESCE(rc.job_2_crew, 0)
                        + COALESCE(rc.job_3_crew, 0)
                        + COALESCE(rc.job_4_crew, 0)
                        + COALESCE(rc.job_5_crew, 0)
                        + COALESCE(rc.job_6_crew, 0)
                        + COALESCE(rc.job_7_crew, 0)
                    FROM stu_rump r
                    LEFT JOIN stu_rumps_cat_role_crew rc
                        ON rc.rump_category_id = r.category_id
                        AND rc.rump_role_id = r.role_id
                    WHERE rbv.rump_id = r.id
            SQL);
        $this->addSql('ALTER TABLE stu_rump_base_values ALTER max_crew DROP DEFAULT');

        $this->addSql('UPDATE stu_crew SET type = CASE type WHEN 1 THEN 2 WHEN 2 THEN 3 WHEN 3 THEN 4 WHEN 4 THEN 5 WHEN 5 THEN 6 WHEN 6 THEN 7 WHEN 7 THEN 1 END');
        $this->addSql('UPDATE stu_crew_assign SET slot = CASE slot WHEN 1 THEN 2 WHEN 2 THEN 3 WHEN 3 THEN 4 WHEN 4 THEN 5 WHEN 5 THEN 6 WHEN 6 THEN 7 WHEN 7 THEN 1 END WHERE slot IS NOT NULL');
        $this->addSql('UPDATE stu_crew_skill SET position = position + 10');
        $this->addSql('UPDATE stu_crew_skill SET position = CASE position WHEN 11 THEN 2 WHEN 12 THEN 3 WHEN 13 THEN 4 WHEN 14 THEN 5 WHEN 15 THEN 6 WHEN 16 THEN 7 WHEN 17 THEN 1 END');
        $this->addSql('UPDATE stu_skill_enhancement SET position = position + 10');
        $this->addSql('UPDATE stu_skill_enhancement SET position = CASE position WHEN 11 THEN 2 WHEN 12 THEN 3 WHEN 13 THEN 4 WHEN 14 THEN 5 WHEN 15 THEN 6 WHEN 16 THEN 7 WHEN 17 THEN 1 END');

        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ADD new_job_1_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ADD new_job_2_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ADD new_job_3_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ADD new_job_4_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ADD new_job_5_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ADD new_job_6_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql('UPDATE stu_rumps_cat_role_crew SET new_job_1_crew = job_7_crew, new_job_2_crew = job_1_crew, new_job_3_crew = job_2_crew, new_job_4_crew = job_3_crew, new_job_5_crew = job_4_crew, new_job_6_crew = job_5_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew DROP job_1_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew DROP job_2_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew DROP job_3_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew DROP job_4_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew DROP job_5_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew DROP job_6_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew DROP job_7_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew RENAME new_job_1_crew TO job_1_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew RENAME new_job_2_crew TO job_2_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew RENAME new_job_3_crew TO job_3_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew RENAME new_job_4_crew TO job_4_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew RENAME new_job_5_crew TO job_5_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew RENAME new_job_6_crew TO job_6_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ALTER job_1_crew DROP DEFAULT');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ALTER job_2_crew DROP DEFAULT');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ALTER job_3_crew DROP DEFAULT');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ALTER job_4_crew DROP DEFAULT');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ALTER job_5_crew DROP DEFAULT');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ALTER job_6_crew DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE stu_crew SET type = CASE type WHEN 1 THEN 7 WHEN 2 THEN 1 WHEN 3 THEN 2 WHEN 4 THEN 3 WHEN 5 THEN 4 WHEN 6 THEN 5 WHEN 7 THEN 6 END');
        $this->addSql('UPDATE stu_crew_assign SET slot = CASE slot WHEN 1 THEN 7 WHEN 2 THEN 1 WHEN 3 THEN 2 WHEN 4 THEN 3 WHEN 5 THEN 4 WHEN 6 THEN 5 WHEN 7 THEN 6 END WHERE slot IS NOT NULL');
        $this->addSql('UPDATE stu_crew_skill SET position = position + 10');
        $this->addSql('UPDATE stu_crew_skill SET position = CASE position WHEN 11 THEN 7 WHEN 12 THEN 1 WHEN 13 THEN 2 WHEN 14 THEN 3 WHEN 15 THEN 4 WHEN 16 THEN 5 WHEN 17 THEN 6 END');
        $this->addSql('UPDATE stu_skill_enhancement SET position = position + 10');
        $this->addSql('UPDATE stu_skill_enhancement SET position = CASE position WHEN 11 THEN 7 WHEN 12 THEN 1 WHEN 13 THEN 2 WHEN 14 THEN 3 WHEN 15 THEN 4 WHEN 16 THEN 5 WHEN 17 THEN 6 END');

        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ADD old_job_1_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ADD old_job_2_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ADD old_job_3_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ADD old_job_4_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ADD old_job_5_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ADD old_job_6_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ADD old_job_7_crew SMALLINT NOT NULL DEFAULT 0');
        $this->addSql(<<<'SQL'
                UPDATE stu_rumps_cat_role_crew rc
                SET old_job_1_crew = rc.job_2_crew,
                    old_job_2_crew = rc.job_3_crew,
                    old_job_3_crew = rc.job_4_crew,
                    old_job_4_crew = rc.job_5_crew,
                    old_job_5_crew = rc.job_6_crew,
                    old_job_6_crew = GREATEST(
                        0,
                        COALESCE(rbv.max_crew - rbv.base_crew, 0)
                            - rc.job_1_crew
                            - rc.job_2_crew
                            - rc.job_3_crew
                            - rc.job_4_crew
                            - rc.job_5_crew
                            - rc.job_6_crew
                    ),
                    old_job_7_crew = rc.job_1_crew
                FROM stu_rump r
                LEFT JOIN stu_rump_base_values rbv ON rbv.rump_id = r.id
                WHERE r.category_id = rc.rump_category_id
                    AND r.role_id = rc.rump_role_id
            SQL);
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew DROP job_1_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew DROP job_2_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew DROP job_3_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew DROP job_4_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew DROP job_5_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew DROP job_6_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew RENAME old_job_1_crew TO job_1_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew RENAME old_job_2_crew TO job_2_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew RENAME old_job_3_crew TO job_3_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew RENAME old_job_4_crew TO job_4_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew RENAME old_job_5_crew TO job_5_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew RENAME old_job_6_crew TO job_6_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew RENAME old_job_7_crew TO job_7_crew');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ALTER job_1_crew DROP DEFAULT');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ALTER job_2_crew DROP DEFAULT');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ALTER job_3_crew DROP DEFAULT');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ALTER job_4_crew DROP DEFAULT');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ALTER job_5_crew DROP DEFAULT');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ALTER job_6_crew DROP DEFAULT');
        $this->addSql('ALTER TABLE stu_rumps_cat_role_crew ALTER job_7_crew DROP DEFAULT');
        $this->addSql('ALTER TABLE stu_rump_base_values DROP max_crew');
    }
}
