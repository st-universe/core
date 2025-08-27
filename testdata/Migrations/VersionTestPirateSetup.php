<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestPirateSetup extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a pirate setup.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_pirate_setup (id, name, probability_weight)
            VALUES (1, \'[b][color = #9b734d]Jäger Rotte[/color][/b]\', 12);'
        );
    }
}
