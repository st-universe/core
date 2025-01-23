<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20241229125148_Lottery_Buildplan_Faction extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add faction_id to stu_lottery_buildplan';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER TABLE stu_lottery_buildplan ADD faction_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {

        $this->addSql('ALTER TABLE stu_lottery_buildplan DROP faction_id');
    }
}
