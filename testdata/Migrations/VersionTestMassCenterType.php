<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestMassCenterType extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_mass_center_type.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_mass_center_type (id, first_field_type_id, description, size)
                VALUES (1060, 106001, \'Roter Zwerg\', 4);
        ');
    }
}