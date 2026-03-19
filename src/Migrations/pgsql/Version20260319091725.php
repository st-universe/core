<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260319091725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stu_rumps_3d_model (width INT NOT NULL, height INT NOT NULL, rotation INT NOT NULL, rump_id INT NOT NULL, PRIMARY KEY (rump_id))');
        $this->addSql('ALTER TABLE stu_rumps_3d_model ADD CONSTRAINT FK_E02312A32EE98D4C FOREIGN KEY (rump_id) REFERENCES stu_rump (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stu_rumps_3d_model DROP CONSTRAINT FK_E02312A32EE98D4C');
        $this->addSql('DROP TABLE stu_rumps_3d_model');
    }
}
