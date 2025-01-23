<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestBuildplans extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_buildplan.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_buildplan (id, rump_id, user_id, name, buildtime, signature, crew)
                VALUES (2324, 6501, 101, \'Bauplan Aerie 19.11.2024 10:11\', 0, \'22f8a48091b2529f88cac1f184db336f\', 11),
                       (2075, 6501, 1, \'Aerie first Coloship\', 0, \'acb5dac65a8fb9b1ec730e38e472e592\', 0),
                       (689, 10053, 1,\'Außenposten Klingonen\',0,\'2c24eaf36486d25346647870121f19b4\', 1);
        ');
    }
}
