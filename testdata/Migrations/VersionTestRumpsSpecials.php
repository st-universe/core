<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestRumpsSpecials extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds rump specials.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_rumps_specials (id, rump_id, special)
                VALUES (1, 6501, 1);
        ');
    }
}
