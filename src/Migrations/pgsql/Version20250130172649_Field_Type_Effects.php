<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250130172649_Field_Type_Effects extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add effects array to map field type.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_map_ftypes ADD effects JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_map_ftypes DROP effects');
    }
}
