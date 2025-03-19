<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250113075911_Skill_Level extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduce crew skill levels';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE stu_crew_skill (crew_id INT NOT NULL, position SMALLINT NOT NULL, expertise INT NOT NULL, PRIMARY KEY(crew_id, position))');
        $this->addSql('CREATE INDEX IDX_5AD19B5F5FE259F6 ON stu_crew_skill (crew_id)');
        $this->addSql('ALTER TABLE stu_crew_skill ADD CONSTRAINT FK_5AD19B5F5FE259F6 FOREIGN KEY (crew_id) REFERENCES stu_crew (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_crew_skill DROP CONSTRAINT FK_5AD19B5F5FE259F6');
        $this->addSql('DROP TABLE stu_crew_skill');
    }
}
