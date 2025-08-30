<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestTholianWeb extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds tholian webs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_tholian_web (id, finished_time)
            VALUES (60001, 1756322053),
                    (60002, null);
        ');
    }
}
