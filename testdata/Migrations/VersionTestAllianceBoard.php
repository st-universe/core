<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestAllianceBoard extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a alliance board.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_alliance_boards (id, alliance_id, name)
                VALUES (1, 2, \'Allianzboard Nr. 1\');
        ');
    }
}
