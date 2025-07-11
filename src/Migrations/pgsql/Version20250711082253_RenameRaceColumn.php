<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250711082253_RenameRaceColumn extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renames the faction reference to faction_id.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user DROP CONSTRAINT fk_12a1701fda6fbbaf
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_12a1701fda6fbbaf
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user RENAME COLUMN race TO faction_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ALTER faction_id TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ADD CONSTRAINT FK_12A1701F4448F8DA FOREIGN KEY (faction_id) REFERENCES stu_factions (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_12A1701F4448F8DA ON stu_user (faction_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user DROP CONSTRAINT FK_12A1701F4448F8DA
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_12A1701F4448F8DA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user RENAME COLUMN faction_id TO race
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ALTER race TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ADD CONSTRAINT fk_12a1701fda6fbbaf FOREIGN KEY (race) REFERENCES stu_factions (id) ON UPDATE RESTRICT ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_12a1701fda6fbbaf ON stu_user (race)
        SQL);
    }
}
