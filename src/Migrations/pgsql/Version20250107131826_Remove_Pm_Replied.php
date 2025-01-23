<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250107131826_Remove_Pm_Replied extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removes the unused attribute stu_pms.replied.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_pms DROP replied');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_pms ADD replied BOOLEAN NOT NULL');
    }
}
