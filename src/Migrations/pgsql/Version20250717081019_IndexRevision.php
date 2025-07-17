<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250717081019_IndexRevision extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Modify some indices to enhance performance.';
    }

    public function up(Schema $schema): void
    {
        // Commodity
        $this->addSql(<<<'SQL'
            CREATE INDEX commodity_sort_idx ON stu_commodity (sort)
        SQL);

        // BuildingCommodity
        $this->addSql(<<<'SQL'
            DROP INDEX commodity_count_idx
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX building_commodity_count_idx ON stu_buildings_commodity (count)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX building_commodity_building_idx RENAME TO IDX_D20755B91485E613
        SQL);

        // PlanetCommodity
        $this->addSql(<<<'SQL'
            CREATE INDEX planet_commodity_count_idx ON stu_planets_commodity (count)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX planet_commodity_commodity_idx ON stu_planets_commodity (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX planet_commodity_unique_idx ON stu_planets_commodity (planet_classes_id, commodity_id)
        SQL);

        // PlanetField
        $this->addSql(<<<'SQL'
            DROP INDEX colony_field_idx
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX sandbox_field_idx
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX planet_field_field_idx ON stu_colonies_fielddata (field_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX colony_building_active_idx
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX sandbox_building_active_idx
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX active_idx RENAME TO planet_field_aktiv_idx
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Commodity
        $this->addSql(<<<'SQL'
            DROP INDEX commodity_sort_idx
        SQL);

        // BuildingCommodity
        $this->addSql(<<<'SQL'
            DROP INDEX building_commodity_count_idx
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX commodity_count_idx ON stu_buildings_commodity (commodity_id, count)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_d20755b91485e613 RENAME TO building_commodity_building_idx
        SQL);

        // PlanetCommodity
        $this->addSql(<<<'SQL'
            DROP INDEX planet_commodity_count_idx
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX planet_commodity_commodity_idx
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX planet_commodity_unique_idx
        SQL);

        // PlanetField
        $this->addSql(<<<'SQL'
            CREATE INDEX colony_field_idx ON stu_colonies_fielddata (colonies_id, field_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX sandbox_field_idx ON stu_colonies_fielddata (colony_sandbox_id, field_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX planet_field_field_idx
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX colony_building_active_idx ON stu_colonies_fielddata (colonies_id, buildings_id, aktiv)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX sandbox_building_active_idx ON stu_colonies_fielddata (colony_sandbox_id, buildings_id, aktiv)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX planet_field_aktiv_idx RENAME TO active_idx
        SQL);
    }
}
