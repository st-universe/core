<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestStorage extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_storage.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_storage (id, commodity_id, count, ship_id, user_id, colony_id, tradepost_id, tradeoffer_id, torpedo_storage_id)
                VALUES (2, 2, 300, NULL, 101, 76777, NULL, NULL, NULL),
                       (3, 4, 150, NULL, 101, 76777, NULL, NULL, NULL),
                       (4, 21, 150, NULL, 101, 76777, NULL, NULL, NULL),
                       (5, 5, 100, NULL, 101, 76777, NULL, NULL, NULL);
        ');
    }
}
