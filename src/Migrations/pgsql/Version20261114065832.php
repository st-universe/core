<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261114065832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE stu_rump_assimilateable (rump_id INT NOT NULL, PRIMARY KEY (rump_id))');
        $this->addSql('CREATE TABLE stu_spacecraft_assimilated (progress INT NOT NULL, initial_progress INT NOT NULL, started_at INT DEFAULT NULL, duration_seconds INT NOT NULL, borg_user_id INT NOT NULL, nanite_commodity_id INT NOT NULL, nanite_reward_min INT NOT NULL, nanite_reward_max INT NOT NULL, completed_at INT DEFAULT NULL, spacecraft_id INT NOT NULL, source_id INT DEFAULT NULL, PRIMARY KEY (spacecraft_id))');
        $this->addSql('CREATE INDEX IDX_AE520D953C1C61 ON stu_spacecraft_assimilated (source_id)');
        $this->addSql('ALTER TABLE stu_rump_assimilateable ADD CONSTRAINT FK_11D114602EE98D4C FOREIGN KEY (rump_id) REFERENCES stu_rump (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE stu_spacecraft_assimilated ADD CONSTRAINT FK_AE520D1C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE stu_spacecraft_assimilated ADD CONSTRAINT FK_AE520D953C1C61 FOREIGN KEY (source_id) REFERENCES stu_spacecraft (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_rump_assimilateable DROP CONSTRAINT FK_11D114602EE98D4C');
        $this->addSql('ALTER TABLE stu_spacecraft_assimilated DROP CONSTRAINT FK_AE520D1C6AF6FD');
        $this->addSql('ALTER TABLE stu_spacecraft_assimilated DROP CONSTRAINT FK_AE520D953C1C61');
        $this->addSql('DROP TABLE stu_rump_assimilateable');
        $this->addSql('DROP TABLE stu_spacecraft_assimilated');
    }
}
