<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250627104311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Correct nullable fields.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_session_strings ALTER date SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_trade_transaction ALTER tradepost_id SET NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_session_strings ALTER date DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_trade_transaction ALTER tradepost_id DROP NOT NULL
        SQL);
    }
}
