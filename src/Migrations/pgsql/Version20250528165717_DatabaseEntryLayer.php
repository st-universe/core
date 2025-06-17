<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250528165717_DatabaseEntryLayer extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add layer_id to stu_database_entrys';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_database_entrys ADD layer_id INT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_database_entrys DROP layer_id
        SQL);
    }
}
