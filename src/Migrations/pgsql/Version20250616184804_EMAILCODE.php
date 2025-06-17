<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250616184804_EMAILCODE extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email_code to stu_user_registration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user_registration ADD email_code VARCHAR(6) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user_registration DROP email_code
        SQL);
    }
}
