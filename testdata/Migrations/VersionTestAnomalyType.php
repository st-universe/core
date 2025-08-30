<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestAnomalyType extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the anomaly types.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_anomaly_type (id,"name",lifespan_in_ticks)
                VALUES (1,\'Subraumellipse\',1),
                    (2,\'Ionensturm\',1000),
                    (1001,\'Adventstür\',120),
                    (1002,\'Oster Ei\',5);'
        );
    }
}
