<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260826190224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stu_user_crew_race (user_id INT NOT NULL, crew_race_id INT NOT NULL, PRIMARY KEY (crew_race_id, user_id))');
        $this->addSql('CREATE INDEX IDX_1E6F8FE85E681894 ON stu_user_crew_race (crew_race_id)');
        $this->addSql('ALTER TABLE stu_user_crew_race ADD CONSTRAINT FK_1E6F8FE85E681894 FOREIGN KEY (crew_race_id) REFERENCES stu_crew_race (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE stu_crew_race DROP CONSTRAINT fk_ed3686294448f8da');
        $this->addSql('DROP INDEX idx_ed3686294448f8da');
        $this->addSql('ALTER TABLE stu_crew_race ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_crew_race ADD shared BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE stu_crew_race ADD accepted BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE stu_crew_race ADD accepted_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_crew_race ALTER faction_id TYPE JSON USING json_build_array(faction_id)');
        $this->addSql('ALTER TABLE stu_crew_race ALTER faction_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stu_user_crew_race DROP CONSTRAINT FK_1E6F8FE85E681894');
        $this->addSql('DROP TABLE stu_user_crew_race');
        $this->addSql('ALTER TABLE stu_crew_race DROP user_id');
        $this->addSql('ALTER TABLE stu_crew_race DROP shared');
        $this->addSql('ALTER TABLE stu_crew_race DROP accepted');
        $this->addSql('ALTER TABLE stu_crew_race DROP accepted_user_id');
        $this->addSql('ALTER TABLE stu_crew_race ALTER faction_id TYPE INT USING (faction_id->>0)::INT');
        $this->addSql('ALTER TABLE stu_crew_race ALTER faction_id SET NOT NULL');
        $this->addSql('ALTER TABLE stu_crew_race ADD CONSTRAINT fk_ed3686294448f8da FOREIGN KEY (faction_id) REFERENCES stu_factions (id) ON UPDATE RESTRICT ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_ed3686294448f8da ON stu_crew_race (faction_id)');
    }
}
