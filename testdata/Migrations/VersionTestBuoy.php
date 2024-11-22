<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestBuoy extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a buoy.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_buoy (id, user_id,"text",location_id) VALUES (42, 101,\'Boje des Todes\',14406);');
    }
}
