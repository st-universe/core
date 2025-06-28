<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250628120755_KnArchiv extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add username to plot members archiv and remove foreign key constraints from kn archiv tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn_archiv DROP CONSTRAINT fk_412525b680d0b01
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn_comments_archiv DROP CONSTRAINT fk_378c11a44b89032c
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_378c11a44b89032c
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_plots_members_archiv ADD username VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_plots_members_archiv DROP username
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn_archiv ADD CONSTRAINT fk_412525b680d0b01 FOREIGN KEY (plot_id) REFERENCES stu_plots_archiv (former_id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn_comments_archiv ADD CONSTRAINT fk_378c11a44b89032c FOREIGN KEY (post_id) REFERENCES stu_kn_archiv (former_id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_378c11a44b89032c ON stu_kn_comments_archiv (post_id)
        SQL);
    }
}
