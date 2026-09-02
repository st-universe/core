<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestUserLastAction extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_user_last_action entries.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_user_last_action (user_id, timestamp)
                VALUES (11, 1731247407),
                       (13, 1731247407),
                       (14, 1731247407),
                       (15, 1731247407),
                       (17, 1731247407),
                       (19, 1731247407),
                       (20, 1731247407),
                       (42, 1732009965),
                       (101, 1732007484),
                       (102, 1732009965),
                       (103, 1611430453),
                       (1, 1710020754),
                       (3, 1611430453),
                       (2, 1611430453),
                       (12, 1731247407),
                       (10, 1731247407);
        ');
    }
}
