<?php

declare(strict_types=1);

namespace Stu\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20241026112423_TutorialRemake2 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tutorial remake 2';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER TABLE stu_tutorial_step DROP CONSTRAINT fk_82d9bf6b4229744f');
        $this->addSql('DROP INDEX idx_82d9bf6b4229744f');
        $this->addSql('ALTER TABLE stu_tutorial_step ADD title TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_tutorial_step ADD text TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_tutorial_step ADD elementIds TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_tutorial_step ADD innerUpdate TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_tutorial_step DROP next_steps');
        $this->addSql('ALTER TABLE stu_tutorial_step DROP payload');
        $this->addSql('ALTER TABLE stu_tutorial_step ALTER view DROP NOT NULL');
        $this->addSql('ALTER TABLE stu_tutorial_step RENAME COLUMN previous_step_id TO fallbackIndex');
    }

    public function down(Schema $schema): void
    {

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE stu_tutorial_step ADD next_steps JSON NOT NULL');
        $this->addSql('ALTER TABLE stu_tutorial_step ADD payload JSON NOT NULL');
        $this->addSql('ALTER TABLE stu_tutorial_step DROP title');
        $this->addSql('ALTER TABLE stu_tutorial_step DROP text');
        $this->addSql('ALTER TABLE stu_tutorial_step DROP elementIds');
        $this->addSql('ALTER TABLE stu_tutorial_step DROP innerUpdate');
        $this->addSql('ALTER TABLE stu_tutorial_step ALTER view SET NOT NULL');
        $this->addSql('ALTER TABLE stu_tutorial_step RENAME COLUMN fallbackIndex TO previous_step_id');
        $this->addSql('ALTER TABLE stu_tutorial_step ADD CONSTRAINT fk_82d9bf6b4229744f FOREIGN KEY (previous_step_id) REFERENCES stu_tutorial_step (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_82d9bf6b4229744f ON stu_tutorial_step (previous_step_id)');
    }
}
