<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250923103840_WormholeRestriction extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds privilege_type and target to wormhole restrictions, makes privilege_type nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_wormhole_restrictions DROP CONSTRAINT fk_76c7b8e0a76ed395');
        $this->addSql('DROP INDEX idx_76c7b8e0a76ed395');
        $this->addSql('ALTER TABLE stu_wormhole_restrictions ADD privilege_type SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_wormhole_restrictions RENAME COLUMN user_id TO target');
        $this->addSql('ALTER TABLE stu_wormhole_restrictions ALTER target TYPE INT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_wormhole_restrictions DROP privilege_type');
        $this->addSql('ALTER TABLE stu_wormhole_restrictions RENAME COLUMN target TO user_id');
        $this->addSql('ALTER TABLE stu_wormhole_restrictions ALTER user_id TYPE INT');
        $this->addSql('ALTER TABLE stu_wormhole_restrictions ADD CONSTRAINT fk_76c7b8e0a76ed395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_76c7b8e0a76ed395 ON stu_wormhole_restrictions (user_id)');
    }
}
