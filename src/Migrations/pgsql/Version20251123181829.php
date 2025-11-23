<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123181829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stu_npc_quest_user ADD CONSTRAINT FK_54C0581A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE stu_opened_advent_door ADD month INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stu_npc_quest_user DROP CONSTRAINT FK_54C0581A76ED395');
        $this->addSql('ALTER TABLE stu_opened_advent_door DROP month');
    }
}
