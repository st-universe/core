<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250623151936_RumpBaseValues extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extracted rump base values from rump entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_rump_base_values (evade_chance SMALLINT NOT NULL, hit_chance SMALLINT NOT NULL, module_level SMALLINT NOT NULL, base_crew SMALLINT NOT NULL, base_eps SMALLINT NOT NULL, base_reactor SMALLINT NOT NULL, base_hull INT NOT NULL, base_shield INT NOT NULL, base_damage SMALLINT NOT NULL, base_sensor_range SMALLINT NOT NULL, base_warpdrive INT NOT NULL, special_slots INT NOT NULL, rump_id INT NOT NULL, PRIMARY KEY(rump_id))
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO stu_rump_base_values 
            (rump_id, evade_chance, hit_chance, module_level, base_crew, base_eps, base_reactor, base_hull, base_shield,
                base_damage, base_sensor_range, base_warpdrive, special_slots)
            SELECT r.id, r.evade_chance, r.hit_chance, r.module_level, r.base_crew, r.base_eps, r.base_reactor, r.base_hull, r.base_shield,
                r.base_damage, r.base_sensor_range, r.base_warpdrive, r.special_slots
            FROM stu_rump r
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump_base_values ADD CONSTRAINT FK_C47ECAED2EE98D4C FOREIGN KEY (rump_id) REFERENCES stu_rump (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump DROP evade_chance
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump DROP hit_chance
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump DROP module_level
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump DROP base_crew
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump DROP base_eps
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump DROP base_reactor
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump DROP base_hull
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump DROP base_shield
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump DROP base_damage
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump DROP base_sensor_range
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump DROP base_warpdrive
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump DROP special_slots
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX rump_role_idx RENAME TO IDX_AD2CDF30D60322AC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX rump_category_idx RENAME TO IDX_AD2CDF3012469DE2
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump_base_values DROP CONSTRAINT FK_C47ECAED2EE98D4C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ADD evade_chance SMALLINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ADD hit_chance SMALLINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ADD module_level SMALLINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ADD base_crew SMALLINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ADD base_eps SMALLINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ADD base_reactor SMALLINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ADD base_hull INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ADD base_shield INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ADD base_damage SMALLINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ADD base_sensor_range SMALLINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ADD base_warpdrive INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ADD special_slots INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_ad2cdf30d60322ac RENAME TO rump_role_idx
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_ad2cdf3012469de2 RENAME TO rump_category_idx
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE stu_rump r
            SET evade_chance = rbv.evade_chance, hit_chance = rbv.hit_chance, module_level = rbv.module_level,
                base_crew = rbv.base_crew, base_eps = rbv.base_eps, base_reactor = rbv.base_reactor,
                base_hull = rbv.base_hull, base_shield = rbv.base_shield, base_damage = rbv.base_damage,
                base_sensor_range = rbv.base_sensor_range, base_warpdrive = rbv.base_warpdrive, special_slots = rbv.special_slots
            FROM stu_rump_base_values rbv
            WHERE r.id = rbv.rump_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_rump_base_values
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ALTER evade_chance SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ALTER hit_chance SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ALTER module_level SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ALTER base_crew SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ALTER base_eps SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ALTER base_reactor SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ALTER base_hull SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ALTER base_shield SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ALTER base_damage SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ALTER base_sensor_range SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ALTER base_warpdrive SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_rump ALTER special_slots SET NOT NULL
        SQL);
    }
}
