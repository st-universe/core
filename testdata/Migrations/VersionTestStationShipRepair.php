<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestStationShipRepair extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a station ship repair.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_station_shiprepair (station_id,ship_id)
            VALUES (43,10203);
        ');
    }
}
