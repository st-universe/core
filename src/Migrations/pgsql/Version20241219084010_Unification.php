<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241219084010_Unification extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Unify rump_id foreign keys.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_research RENAME COLUMN rumps_id TO rump_id');

        $this->addSql('ALTER TABLE stu_rumps_specials DROP CONSTRAINT fk_3f94d9b298355913');
        $this->addSql('DROP INDEX rump_special_ship_rump_idx');
        $this->addSql('ALTER TABLE stu_rumps_specials RENAME COLUMN rumps_id TO rump_id');
        $this->addSql('ALTER TABLE stu_rumps_specials ADD CONSTRAINT FK_3F94D9B22EE98D4C FOREIGN KEY (rump_id) REFERENCES stu_rump (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX rump_special_ship_rump_idx ON stu_rumps_specials (rump_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_rumps_specials DROP CONSTRAINT FK_3F94D9B22EE98D4C');
        $this->addSql('DROP INDEX rump_special_ship_rump_idx');
        $this->addSql('ALTER TABLE stu_rumps_specials RENAME COLUMN rump_id TO rumps_id');
        $this->addSql('ALTER TABLE stu_rumps_specials ADD CONSTRAINT fk_3f94d9b298355913 FOREIGN KEY (rumps_id) REFERENCES stu_rump (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX rump_special_ship_rump_idx ON stu_rumps_specials (rumps_id)');

        $this->addSql('ALTER TABLE stu_research RENAME COLUMN rump_id TO rumps_id');
    }
}
