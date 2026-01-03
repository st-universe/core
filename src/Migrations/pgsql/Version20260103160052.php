<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260103160052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_tutorial_step RENAME COLUMN elementids TO element_ids');
        $this->addSql('ALTER TABLE stu_tutorial_step RENAME COLUMN innerupdate TO inner_update');
        $this->addSql('ALTER TABLE stu_tutorial_step RENAME COLUMN fallbackindex TO fallback_index');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_tutorial_step RENAME COLUMN element_ids TO elementids');
        $this->addSql('ALTER TABLE stu_tutorial_step RENAME COLUMN inner_update TO innerupdate');
        $this->addSql('ALTER TABLE stu_tutorial_step RENAME COLUMN fallback_index TO fallbackindex');
    }
}
