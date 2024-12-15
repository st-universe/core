<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestTrumfield extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_trumfield.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_trumfield (id, huelle, former_rump_id, location_id)
            VALUES (1, 42, 6501, 15247);
        ');
    }
}
