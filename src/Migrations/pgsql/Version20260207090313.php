<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260207090313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renames stu_colonies_fielddata.colonies_id to colony_id and adjusts the foreign key constraint accordingly.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_colonies_fielddata DROP CONSTRAINT fk_7e4f109774eb5cc8');
        $this->addSql('DROP INDEX idx_7e4f109774eb5cc8');
        $this->addSql('ALTER TABLE stu_colonies_fielddata RENAME COLUMN colonies_id TO colony_id');
        $this->addSql('ALTER TABLE stu_colonies_fielddata ADD CONSTRAINT FK_7E4F109796ADBADE FOREIGN KEY (colony_id) REFERENCES stu_colony (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_7E4F109796ADBADE ON stu_colonies_fielddata (colony_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_colonies_fielddata DROP CONSTRAINT FK_7E4F109796ADBADE');
        $this->addSql('DROP INDEX IDX_7E4F109796ADBADE');
        $this->addSql('ALTER TABLE stu_colonies_fielddata RENAME COLUMN colony_id TO colonies_id');
        $this->addSql('ALTER TABLE stu_colonies_fielddata ADD CONSTRAINT fk_7e4f109774eb5cc8 FOREIGN KEY (colonies_id) REFERENCES stu_colony (id) ON UPDATE RESTRICT ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_7e4f109774eb5cc8 ON stu_colonies_fielddata (colonies_id)');
    }
}
