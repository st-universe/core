<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250112193414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop obsolete defaults and index.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX entry_user_idx');
        $this->addSql('ALTER TABLE stu_ship_log ALTER is_private DROP DEFAULT');
        $this->addSql('ALTER TABLE stu_user ALTER deals DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_ship_log ALTER is_private SET DEFAULT false');
        $this->addSql('ALTER TABLE stu_user ALTER deals SET DEFAULT false');
        $this->addSql('CREATE UNIQUE INDEX entry_user_idx ON stu_database_user (database_id, user_id)');
    }
}
