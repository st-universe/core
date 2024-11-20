<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestSystemTypes extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_system_types.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_system_types (id, description, database_id, is_generateable, first_mass_center_id, second_mass_center_id) VALUES (1060, \'Roter Zwerg\', 6901060, true, 1060, NULL);
        ');
    }
}
