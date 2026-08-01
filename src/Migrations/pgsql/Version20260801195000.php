<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260801195000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replaces the crew rank hierarchy with Cadet, Crewman, Ensign, Lieutenant, Commander, Captain, Commodore and Admiral ranks';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE stu_crew SET rank = CASE rank
            WHEN 'RECRUIT' THEN 'CADET'
            WHEN 'CADET' THEN 'CREWMAN'
            WHEN 'SENIOR_COMMANDER' THEN 'CAPTAIN'
            ELSE rank
        END");
        $this->addSql("ALTER TABLE stu_crew ALTER rank SET DEFAULT 'CADET'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE stu_crew SET rank = CASE rank
            WHEN 'CADET' THEN 'RECRUIT'
            WHEN 'CREWMAN' THEN 'CADET'
            WHEN 'CAPTAIN' THEN 'SENIOR_COMMANDER'
            ELSE rank
        END");
        $this->addSql("ALTER TABLE stu_crew ALTER rank SET DEFAULT 'RECRUIT'");
    }
}
