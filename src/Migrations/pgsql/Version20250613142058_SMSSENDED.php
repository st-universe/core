<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250613142058_SMSSENDED extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds column sms_sended to stu_user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ADD sms_sended INT DEFAULT 1
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user DROP sms_sended
        SQL);
    }
}
