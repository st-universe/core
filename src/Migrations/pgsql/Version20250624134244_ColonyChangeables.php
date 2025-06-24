<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250624134244_ColonyChangeables extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extract colony changeable values from colony entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_colonies RENAME TO stu_colony');

        // create new table
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_colony_changeable
                (bev_work INT NOT NULL, bev_free INT NOT NULL, bev_max INT NOT NULL, eps INT NOT NULL, max_eps INT NOT NULL,
                max_storage INT NOT NULL, populationlimit INT NOT NULL, immigrationstate BOOLEAN NOT NULL, shields INT DEFAULT NULL,
                shield_frequency INT DEFAULT NULL, colony_id INT NOT NULL, torpedo_type INT DEFAULT NULL, PRIMARY KEY(colony_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_39D2AE0F942323E3 ON stu_colony_changeable (torpedo_type)
        SQL);

        // migrate data
        $this->addSql(<<<'SQL'
            INSERT INTO stu_colony_changeable 
                (colony_id, bev_work, bev_free, bev_max, eps, max_eps, max_storage,
                populationlimit, immigrationstate, shields, shield_frequency, torpedo_type)
            SELECT id, bev_work, bev_free, bev_max, eps, max_eps, max_storage, populationlimit,
                immigrationstate, shields, shield_frequency, torpedo_type
            FROM stu_colony c
        SQL);

        // set constraints
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony_changeable ADD CONSTRAINT FK_39D2AE0F96ADBADE FOREIGN KEY (colony_id) REFERENCES stu_colony (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony_changeable ADD CONSTRAINT FK_39D2AE0F942323E3 FOREIGN KEY (torpedo_type) REFERENCES stu_torpedo_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);

        // clean colony table
        $this->cleanColonyTable();
    }

    private function cleanColonyTable(): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony DROP CONSTRAINT fk_d1c60f73942323e3
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_d1c60f73942323e3
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX colony_sys_map_idx
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony DROP bev_work
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony DROP bev_free
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony DROP bev_max
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony DROP eps
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony DROP max_eps
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony DROP max_storage
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony DROP populationlimit
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony DROP immigrationstate
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony DROP shields
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony DROP shield_frequency
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony DROP torpedo_type
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX colony_classes_idx RENAME TO IDX_D1C60F739106126
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX colony_user_idx RENAME TO IDX_D1C60F73A76ED395
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_colony RENAME TO stu_colonies');

        // reset colony
        $this->resetColonyFields();

        // migrate data
        $this->addSql(<<<'SQL'
            UPDATE stu_colonies c
            SET bev_work = cc.bev_work, bev_free = cc.bev_free, bev_max = cc.bev_max, eps = cc.eps, max_eps = cc.max_eps,
                max_storage = cc.max_storage, populationlimit = cc.populationlimit, immigrationstate = cc.immigrationstate,
                shields = cc.shields, shield_frequency = cc.shield_frequency, torpedo_type = cc.torpedo_type
            FROM stu_colony_changeable cc
            WHERE c.id = cc.colony_id
        SQL);

        // finalize colony
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ALTER bev_work SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ALTER bev_free SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ALTER bev_max SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ALTER eps SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ALTER max_eps SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ALTER max_storage SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ALTER populationlimit SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ALTER immigrationstate SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ADD CONSTRAINT fk_d1c60f73942323e3 FOREIGN KEY (torpedo_type) REFERENCES stu_torpedo_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_d1c60f73942323e3 ON stu_colonies (torpedo_type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX colony_sys_map_idx ON stu_colonies (starsystem_map_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_d1c60f73a76ed395 RENAME TO colony_user_idx
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_d1c60f739106126 RENAME TO colony_classes_idx
        SQL);

        // clear entity
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony_changeable DROP CONSTRAINT FK_39D2AE0F96ADBADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colony_changeable DROP CONSTRAINT FK_39D2AE0F942323E3
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_colony_changeable
        SQL);
    }

    private function resetColonyFields(): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ADD bev_work INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ADD bev_free INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ADD bev_max INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ADD eps INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ADD max_eps INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ADD max_storage INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ADD populationlimit INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ADD immigrationstate BOOLEAN DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ADD shields INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ADD shield_frequency INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_colonies ADD torpedo_type INT DEFAULT NULL
        SQL);
    }
}
