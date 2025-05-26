<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250526153605_HistoryWithLocation extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds optional location reference to history entries.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_history ADD location_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_history ADD CONSTRAINT FK_7F01683964D218E FOREIGN KEY (location_id) REFERENCES stu_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7F01683964D218E ON stu_history (location_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_history DROP CONSTRAINT FK_7F01683964D218E
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_7F01683964D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_history DROP location_id
        SQL);
    }
}
