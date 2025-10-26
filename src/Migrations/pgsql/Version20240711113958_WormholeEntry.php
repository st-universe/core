<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240711113958_WormholeEntry extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Convert wormhole entry type to enum string.';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_wormhole_entry ALTER type TYPE VARCHAR(10)');

        $this->addSql('UPDATE stu_wormhole_entry SET type = \'MAP -> W\' WHERE type = \'0\'');
        $this->addSql('UPDATE stu_wormhole_entry SET type = \'W -> MAP\' WHERE type = \'1\'');
        $this->addSql('UPDATE stu_wormhole_entry SET type = \'MAP <-> W\' WHERE type = \'2\'');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE stu_wormhole_entry SET type = \'0\' WHERE type = \'MAP -> W\'');
        $this->addSql('UPDATE stu_wormhole_entry SET type = \'1\' WHERE type = \'W -> MAP\'');
        $this->addSql('UPDATE stu_wormhole_entry SET type = \'2\' WHERE type = \'MAP <-> W\'');

        $this->addSql('ALTER TABLE stu_wormhole_entry ALTER type TYPE SMALLINT USING type::smallint');
    }
}
