<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260812095111 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stu_skill_enhancement ADD cooldown INT DEFAULT NULL');
        $this->addSql('DROP INDEX skill_enhancement_log_crew_date_idx');
        $this->addSql('CREATE INDEX skill_enhancement_log_crew_date_idx ON stu_skill_enhancement_log (crew_id, enhancement_id, date)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stu_skill_enhancement DROP cooldown');
        $this->addSql('DROP INDEX skill_enhancement_log_crew_date_idx');
        $this->addSql('CREATE INDEX skill_enhancement_log_crew_date_idx ON stu_skill_enhancement_log (crew_id, date)');
    }
}
