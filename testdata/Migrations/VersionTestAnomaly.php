<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestAnomaly extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add anomalies.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_anomaly (id, anomaly_type_id,remaining_ticks,location_id,parent_id,data)
                        VALUES (1,1001,647,15247,NULL,NULL),
                                (2,1002,647,15247,NULL,NULL);
            '
        );
    }
}
