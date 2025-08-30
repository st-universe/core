<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestSpacecraftCondition extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_spacecraft_condition.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_spacecraft_condition (spacecraft_id, hull, shield, is_disabled, state)
            VALUES (42, 819, 819, 0, 0),
                    (77, 819, 819, 0, 0),
                    (78, 800, 819, 0, 0),
                    (79, 819, 819, 0, 0),
                    (80, 819, 819, 0, 0),
                    (81, 819, 819, 0, 0),
                    (43, 20000, 24000, 0, 0),
                    (1021, 819, 819, 0, 0),
                    (1022, 819, 819, 0, 0),
                    (10203, 819, 819, 0, 0),
                    (1023, 819, 819, 0, 0),
                    (1024, 819, 819, 0, 7),
                    (1025, 819, 819, 0, 7),
                    (1026, 819, 819, 0, 0),
                    (60001, 40, 0, 0, 0),
                    (60002, 80, 80, 0, 0),
                    (100001, 81, 81, 0, 0);
        ');
    }
}
