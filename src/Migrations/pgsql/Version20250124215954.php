<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250124215954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove obsolete attributes.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_spacecraft DROP sensor_range');
        $this->addSql('ALTER TABLE stu_spacecraft DROP shield_regeneration_timer');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_spacecraft ADD sensor_range SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE stu_spacecraft ADD shield_regeneration_timer INT NOT NULL');
    }
}
