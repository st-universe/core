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
        $this->addSql('INSERT INTO stu_storage (id, commodity_id, count, spacecraft_id, user_id, colony_id, tradepost_id, tradeoffer_id, torpedo_storage_id)
                VALUES (2, 2, 300, NULL, 101, 42, NULL, NULL, NULL),
                       (3, 4, 150, NULL, 101, 42, NULL, NULL, NULL),
                       (4, 21, 150, NULL, 101, 42, NULL, NULL, NULL),
                       (5, 5, 100, NULL, 101, 42, NULL, NULL, NULL),
                       (6, 21, 99, 42, 101, NULL, NULL, NULL, NULL),
                       (7, 8, 42, 42, 101, NULL, NULL, NULL, NULL),
                       (8, 20061, 1, 77, 101, NULL, NULL, NULL, NULL),
                       (9, 20062, 1, NULL, 101, 42, NULL, NULL, NULL),
                       (10, 8, 15, 10203, 102, NULL, NULL, NULL, NULL),
                       (11, 20061, 1, 42, 101, NULL, NULL, NULL, NULL),
                       (12, 10506, 14, NULL, 101, NULL, NULL, 1, NULL),
                       (13, 8, 1, NULL, 101, NULL, 2, NULL, NULL);
        ');
    }
}
