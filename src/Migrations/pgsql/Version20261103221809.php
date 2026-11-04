<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261103221809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Interaktive Missionsseiten und feste Zufallsfunde';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_away_mission_dialogue ADD show_surface BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE stu_away_mission_progress ADD research_system VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_away_mission_progress ADD reporting_crew_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_away_mission_progress ADD contact_faction_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_away_mission_progress ADD CONSTRAINT FK_76A3F17483F1A14D FOREIGN KEY (contact_faction_id) REFERENCES stu_factions (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_76A3F17483F1A14D ON stu_away_mission_progress (contact_faction_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_away_mission_dialogue DROP show_surface');
        $this->addSql('ALTER TABLE stu_away_mission_progress DROP CONSTRAINT FK_76A3F17483F1A14D');
        $this->addSql('DROP INDEX IDX_76A3F17483F1A14D');
        $this->addSql('ALTER TABLE stu_away_mission_progress DROP research_system');
        $this->addSql('ALTER TABLE stu_away_mission_progress DROP reporting_crew_id');
        $this->addSql('ALTER TABLE stu_away_mission_progress DROP contact_faction_id');
    }
}
