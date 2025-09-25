<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250925113902_PMFormerUser extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds former user fields to private messages';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_pms ADD former_send_user INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_pms ADD former_recip_user INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_pms DROP former_send_user');
        $this->addSql('ALTER TABLE stu_pms DROP former_recip_user');
    }
}