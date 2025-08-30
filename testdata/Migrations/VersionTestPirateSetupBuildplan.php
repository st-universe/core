<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestPirateSetupBuildplan extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds pirate setup buildplans.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_pirate_setup_buildplan (pirate_setup_id,buildplan_id,amount)
            VALUES (1,2324,9);'
        );
    }
}
