<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250426100232_KN_DEL extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add deleted column to kn table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_kn ADD deleted INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_kn DROP deleted');
    }
}
