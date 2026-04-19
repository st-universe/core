<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestColonyShipRepair extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a colony ship repair.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_colonies_shiprepair (colony_id,ship_id,field_id,finish_time,stop_date,is_stopped)
            VALUES (42,78,2,0,0,FALSE);
        ');
    }
}
