<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250304213810_Field_Type_Complementary_Color extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds complementary color to field types';;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_map_ftypes ADD complementary_color VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_map_ftypes DROP complementary_color');
    }
}
