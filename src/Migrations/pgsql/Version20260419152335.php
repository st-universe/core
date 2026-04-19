<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260419152335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stu_award ADD is_npc BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_award ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_award ADD CONSTRAINT FK_8CCE880A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX award_user_idx ON stu_award (user_id)');
        $this->addSql('CREATE INDEX award_is_npc_idx ON stu_award (is_npc)');
        $this->addSql('ALTER TABLE stu_user_award ADD count INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stu_award DROP CONSTRAINT FK_8CCE880A76ED395');
        $this->addSql('DROP INDEX award_user_idx');
        $this->addSql('DROP INDEX award_is_npc_idx');
        $this->addSql('ALTER TABLE stu_award DROP is_npc');
        $this->addSql('ALTER TABLE stu_award DROP user_id');
        $this->addSql('ALTER TABLE stu_user_award DROP count');
    }
}
