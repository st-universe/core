<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Override;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240816121145_DatabaseEntry extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add database entry foreign keys and indices';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_ships ALTER database_id DROP NOT NULL');
        $this->addSql('UPDATE stu_ships SET database_id = null WHERE database_id = 0');
        $this->addSql('ALTER TABLE stu_colonies ADD CONSTRAINT FK_D1C60F73F0AA09DB FOREIGN KEY (database_id) REFERENCES stu_database_entrys (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D1C60F73F0AA09DB ON stu_colonies (database_id)');
        $this->addSql('ALTER TABLE stu_ships ADD CONSTRAINT FK_A560DD56F0AA09DB FOREIGN KEY (database_id) REFERENCES stu_database_entrys (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A560DD56F0AA09DB ON stu_ships (database_id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_ships DROP CONSTRAINT FK_A560DD56F0AA09DB');
        $this->addSql('DROP INDEX UNIQ_A560DD56F0AA09DB');
        $this->addSql('ALTER TABLE stu_colonies DROP CONSTRAINT FK_D1C60F73F0AA09DB');
        $this->addSql('DROP INDEX UNIQ_D1C60F73F0AA09DB');
    }
}
