<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250628195910_KnArchivRefs extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add refs column to stu_kn_archiv';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn_archiv ADD refs JSON DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_plots_members_archiv DROP CONSTRAINT fk_cde3d430680d0b01
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_cde3d430680d0b01
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_plots_members_archiv ADD CONSTRAINT fk_cde3d430680d0b01 FOREIGN KEY (plot_id) REFERENCES stu_plots_archiv (former_id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_cde3d430680d0b01 ON stu_plots_members_archiv (plot_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn_archiv DROP refs
        SQL);
    }
}
