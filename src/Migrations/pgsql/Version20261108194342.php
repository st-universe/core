<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261108194342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Feldgebundene Sonderforschung und Naniten-Zwischenfälle';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_special_research ADD field_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_special_research ADD building_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_special_research ADD failure_settings JSON DEFAULT NULL');
        $this->addSql('CREATE INDEX special_research_field_idx ON stu_special_research (field_id, status)');
        $this->addSql('ALTER TABLE stu_special_research_definition ADD failure_damage_percent SMALLINT DEFAULT 15 NOT NULL');
        $this->addSql('ALTER TABLE stu_special_research_definition ADD failure_worker_min_percent SMALLINT DEFAULT 25 NOT NULL');
        $this->addSql('ALTER TABLE stu_special_research_definition ADD failure_worker_max_percent SMALLINT DEFAULT 75 NOT NULL');
        $this->addSql('ALTER TABLE stu_special_research_definition ADD failure_population_min SMALLINT DEFAULT 3 NOT NULL');
        $this->addSql('ALTER TABLE stu_special_research_definition ADD failure_population_max SMALLINT DEFAULT 12 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX special_research_field_idx');
        $this->addSql('ALTER TABLE stu_special_research DROP field_id');
        $this->addSql('ALTER TABLE stu_special_research DROP building_id');
        $this->addSql('ALTER TABLE stu_special_research DROP failure_settings');
        $this->addSql('ALTER TABLE stu_special_research_definition DROP failure_damage_percent');
        $this->addSql('ALTER TABLE stu_special_research_definition DROP failure_worker_min_percent');
        $this->addSql('ALTER TABLE stu_special_research_definition DROP failure_worker_max_percent');
        $this->addSql('ALTER TABLE stu_special_research_definition DROP failure_population_min');
        $this->addSql('ALTER TABLE stu_special_research_definition DROP failure_population_max');
    }
}
