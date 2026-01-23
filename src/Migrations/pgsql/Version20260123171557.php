<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260123171557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stu_station ADD ally_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_station ADD CONSTRAINT FK_C782E0C31C6E3E76 FOREIGN KEY (ally_id) REFERENCES stu_alliances (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_C782E0C31C6E3E76 ON stu_station (ally_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stu_station DROP CONSTRAINT FK_C782E0C31C6E3E76');
        $this->addSql('DROP INDEX IDX_C782E0C31C6E3E76');
        $this->addSql('ALTER TABLE stu_station DROP ally_id');
    }
}
