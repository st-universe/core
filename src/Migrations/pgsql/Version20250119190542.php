<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250119190542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make anomaly location nullable.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_anomaly ALTER location_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_anomaly ALTER location_id SET NOT NULL');
    }
}
