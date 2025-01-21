<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250116093845_Anomaly_Data extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add anomaly data attribute.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_anomaly ADD data TEXT DEFAULT NULL');
        $this->addSql('DELETE FROM stu_anomaly WHERE remaining_ticks = 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_anomaly DROP data');
    }
}
