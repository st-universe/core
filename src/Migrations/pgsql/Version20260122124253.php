<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260122124253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stu_alliances_jobs DROP is_founder_permission');
        $this->addSql('ALTER TABLE stu_alliances_jobs DROP is_successor_permission');
        $this->addSql('ALTER TABLE stu_alliances_jobs DROP is_diplomatic_permission');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stu_alliances_jobs ADD is_founder_permission BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE stu_alliances_jobs ADD is_successor_permission BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE stu_alliances_jobs ADD is_diplomatic_permission BOOLEAN DEFAULT false NOT NULL');
    }
}
