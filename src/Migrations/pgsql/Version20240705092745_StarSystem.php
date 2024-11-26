<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Override;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240705092745_StarSystem extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Removes the following obsolete fields from stu_systems: cx, cy';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX coordinate_idx;');
        $this->addSql('ALTER TABLE stu_systems DROP cx;');
        $this->addSql('ALTER TABLE stu_systems DROP cy;');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_systems ADD cx SMALLINT DEFAULT NULL;');
        $this->addSql('ALTER TABLE stu_systems ADD cy SMALLINT DEFAULT NULL;');
        $this->addSql('CREATE INDEX coordinate_idx ON stu_systems (cx, cy);');
    }
}
