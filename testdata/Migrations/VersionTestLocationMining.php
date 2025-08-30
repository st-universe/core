<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestLocationMining extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a location mining.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_location_mining (id,location_id,commodity_id,actual_amount,max_amount,depleted_at)
                    VALUES (1488763,14887,63,1522,1522,NULL);
        ');
    }
}
