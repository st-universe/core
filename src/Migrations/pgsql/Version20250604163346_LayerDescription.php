<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250604163346_LayerDescription extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add description, is_colonizable and is_noobzone to layer';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_layer ADD description TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_layer ADD is_colonizable BOOLEAN DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_layer ADD is_noobzone BOOLEAN DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_layer DROP description
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_layer DROP is_colonizable
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_layer DROP is_noobzone
        SQL);
    }
}
