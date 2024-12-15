<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestStation extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_station.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_station (id, influence_area_id)
            VALUES (43, NULL);
        ');
    }
}
